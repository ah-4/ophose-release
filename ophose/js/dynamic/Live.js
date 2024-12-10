class Live {

    static __currentReadingStyleComponent = undefined;
    static __calledLives = {};

    __processValue(value, thisArg = this) {
        if(typeof value === 'object' && value !== null && !Array.isArray(value) && value.constructor === Object) {
            thisArg.__valueIsObject = true;
            let usedValues = new Set();
            for(let key in value) {
                usedValues.add(key);
                if(thisArg[key] instanceof Live) {
                    thisArg[key].set(value[key]);
                } else {
                    thisArg[key] = new Live(value[key]).setParent(thisArg);
                }
                thisArg.__objectUsedKeys.add(key);
            }
            if(!thisArg.__keepValuesOnUpdateIfNotInObject) {
                for(let key of thisArg.__objectUsedKeys) {
                    if(!usedValues.has(key)) {
                        thisArg[key].set(undefined);
                    }
                }
            }
            return;
        }
        this.__valueIsObject = false;
        thisArg.__value = value;
    }

    constructor(value) { 
        this.__objectUsedKeys = new Set();
        this.__valueIsObject = false;
        this.__processValue(value);
        this.__placedStyleComponents = [];
        this.__dynCallbacks = [];
        this.__callbackListeners = [];
        this.__ruleId = '' + Math.random();
        this.__rules = [];
        this.__rulesDependencies = [];
        this.__updateOnlyIfValueChanges = true;
        this.__keepValuesOnUpdateIfNotInObject = true;
    }

    /**
     * Either keeps values on update if not in object or not
     * when the object receives a new value
     * @param {boolean} value the value
     * @returns the live variable
     */
    keepValues(value) {
        this.__keepValuesOnUpdateIfNotInObject = value;
        return this;
    }

    /**
     * Sets the parent of the live variable
     * 
     * @param {Live} parent the parent
     * @returns the live variable
     */
    setParent(parent) {
        this.__parent = parent;
        return this;
    }

    /**
     * Returns the value
     */
    get value() {
        return this.get();
    }

    /**
     * Sets the value
     * @param {*} value the value
     */
    set value(value) {
        this.set(value);
    }

    /**
     * Adds a callback listener
     * @param {function} callback the callback
     * @returns {Live} the live variable
     */
    subscribe(callback) {
        this.__callbackListeners.push(callback);
        return this;
    }

    /**
     * Removes a callback listener
     * 
     * @param {function} callback the callback
     * @returns the callback
     */
    unsubscribe(callback) {
        this.__callbackListeners.splice(this.__callbackListeners.indexOf(callback), 1);
    }

    /**
     * Toggles the value if it is a boolean
     * 
     * @returns the value or undefined if not a boolean
     */
    toggle() {
        if(typeof this.__value != 'boolean') {
            dev.error('Cannot toggle a non-boolean value', this.__value);
            return;
        };
        this.set(!this.__value);
        return this.__value;
    }

    /**
     * Returns live value and automatically assigns as observer of this
     * @returns the value
     */
    get() {
        if (this.__valueIsObject) {
            let obj = {};
            for(let key of this.__objectUsedKeys) {
                obj[key] = this[key].get();
            }
            return obj;
        }
        if (Live.__currentReadingStyleComponent) {
            let component = Live.__currentReadingStyleComponent;
            this.__placedStyleComponents.push(component);
            component.__lives.push(this);
        }
        for(let id in Live.__calledLives) {
            if(id === this.__ruleId) continue;
            Live.__calledLives[id].push(this);
        }
        if(typeof this.__value === 'object') return Live.flatten(this.__value);
        return this.__value;
    }

    /**
     * Updates the value
     * @param {*} value the value
     */
    set(value) { 
        let oldValue = this.__value;
        this.__processValue(value);
        for(let rule of this.__rules) {
            this.__value = rule(this.__value, oldValue);
        }
        if(this.__updateOnlyIfValueChanges) {
            if(typeof this.__value !== 'object') if(this.__value === oldValue) return;
        }
        Live.__onValueChange(this, this.__value, oldValue);
    }

    refresh() {
        Live.__onValueChange(this, this.__value, this.__value);
    }

    /**
     * Updates the value with a callback
     * @param {function} callback the callback (takes the current value as argument and should return the new value)
     * @returns the value
     */
    update(callback) {
        let newValue = callback(this.__value);
        this.set(newValue);
        return newValue;
    }

    /**
     * Adds a rule to the live variable (a rule is a function that takes the current value and returns the new value)
     * @param {*} callback the callback
     * @returns the live variable
     */
    rule(callback) {
        this.__rules.push(callback);
        Live.__calledLives[this.__ruleId] = [];
        callback(this.__value, this.__value);
        for(let live of Live.__calledLives[this.__ruleId]) {
            if(this.__rulesDependencies.indexOf(live) === -1) {
                this.__rulesDependencies.push(live);
                live.subscribe(() => {
                    this.set(this.__value);
                });
            }
        }
        // Remove key from called lives
        delete Live.__calledLives[this.__ruleId];
        return this;
    }

    /**
     * Adds minimum value rule to the live variable
     * @param {number} min the minimum value 
     * @returns the live variable
     */
    min(min) {
        if(min instanceof Live) {
            min.subscribe(() => this.set(this.__value));
            return this.rule((value) => Math.max(value, min.get()));
        }
        return this.rule((value) => Math.max(value, min));
    }

    /**
     * Adds maximum value rule to the live variable
     * @param {number} max the maximum value
     * @returns the live variable
     */
    max(max) {
        if(max instanceof Live) {
            max.subscribe(() => this.set(this.__value));
            return this.rule((value) => Math.min(value, max.get()));
        }
        return this.rule((value) => Math.min(value, max));
    }

    /**
     * Updates the value only if the value changes
     * @param {boolean} value the value
     * @returns the live variable
     */
    updateOnlyIfValueChanges(value) {
        this.__updateOnlyIfValueChanges = value;
        return this;
    }

    /**
     * Adds a value to the current value
     * @param {*} value the value to add
     */
    add(value) {
        if(Array.isArray(this.__value)) {
            this.__value.push(value);
            this.set(this.__value);
            return;
        }
        this.set(value + this.__value);
    }

    /**
     * Removes a value from the current value
     * @param {*} value the value to remove
     */
    remove(value) {
        if(Array.isArray(this.__value)) {
            this.__value.splice(this.__value.indexOf(value), 1);
            this.set(this.__value);
            return;
        }
        this.set(this.__value - value);
    }

    removeAt(index) {
        if(Array.isArray(this.__value)) {
            this.__value.splice(index, 1);
            this.set(this.__value);
            return;
        }
        dev.error('Cannot remove at index from a non-array value', this.__value);
    }

    /**
     * Called when value changes and process needed updates
     * @param {*} liveVar the live variable
     * @param {*} newValue the new value
     * @param {*} oldValue the old value
     */
    static __onValueChange(liveVar, newValue, oldValue) {
        for (let callback of liveVar.__callbackListeners) callback(newValue, oldValue);
        for (let component of liveVar.__placedStyleComponents) component.___reloadStyle();
        if(liveVar.__parent) Live.__onValueChange(liveVar.__parent, liveVar.__parent.__value, liveVar.__parent.__value);
    }

    /**
     * Local lives loaded from local storage
     */
    static __localLives = {};

    /**
     * Returns a live variable from a local storage key
     * 
     * @param {*} key the key
     * @param {*} defaultValue the default value if the key is not set
     * @returns {Live} the live variable
     */
    static local(key, defaultValue = null) {
        key = '__local_live.' + key;
        let value = localStorage.getItem(key);
        let live = Live.__localLives[key];
    
        if(value === null) {
            localStorage.setItem(key, defaultValue);
            value = defaultValue;
        }
    
        if(live === undefined) {
            live = new Live(value);
            live.subscribe((newValue) => {
                localStorage.setItem(key, newValue); 
            });
            Live.__localLives[key] = live;
        }
    
        return live;
    }

    /**
     * Flattens an object with lives (replaces lives with their values recursively)
     * @param {*} obj the object
     * @returns the flattened object
     */
    static flatten(obj) {
        return JSON.parse(JSON.stringify(obj, (key, value) => {
            if(value instanceof Live) return value.get();
            return value;
        }));
    }

    // For arrays
    // #region Array methods
    /**
     * Adds values to the array
     * @param  {...any} values the values
     * @returns the length of the array
     */
    push(...values) {
        let a = [];
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot push to a non-array value', this.__value);
            return;
        }
        this.set(this.__value.concat(values));
        return this.__value.length;
    }

    /**
     * Removes the last value from the array
     * @returns the removed value
     */
    pop() {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot pop from a non-array value', this.__value);
            return;
        }
        let value = this.__value.pop();
        this.set(this.__value);
        return value;
    }

    /**
     * Removes the first value from the array
     * @returns the removed value
     */
    shift() {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot shift from a non-array value', this.__value);
            return;
        }
        let value = this.__value.shift();
        this.set(this.__value);
        return value;
    }

    /**
     * Adds values to the beginning of the array
     * @param  {...any} values the values
     * @returns the length of the array
     */
    unshift(...values) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot unshift to a non-array value', this.__value);
            return;
        }
        this.set(values.concat(this.__value));
        return this.__value.length;
    }

    /**
     * Removes values from the array
     * @param {number} index the index
     * @param {number} howMany the number of values to remove
     * @returns the removed values
     */
    splice(index, howMany) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot splice from a non-array value', this.__value);
            return;
        }
        let removed = this.__value.splice(index, howMany);
        this.set(this.__value);
        return removed;
    }

    /**
     * Reverses the array
     */
    reverse() {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot reverse a non-array value', this.__value);
            return;
        }
        this.set(this.__value.reverse());
    }

    /**
     * Sorts the array
     * @param {function} compareFunction the compare function
     */
    sort(compareFunction) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot sort a non-array value', this.__value);
            return;
        }
        this.set(this.__value.sort(compareFunction));
    }

    /**
     * Joins the array
     * @param {string} separator the separator
     * @returns the joined string
     */
    join(separator) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot join a non-array value', this.__value);
            return;
        }
        return this.__value.join(separator);
    }

    /**
     * Slices the array
     * @param {number} start the start index
     * @param {number} end the end index
     * @returns the sliced array
     */
    slice(start, end) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot slice a non-array value', this.__value);
            return;
        }
        return this.__value.slice(start, end);
    }

    /**
     * Filters the array
     * @param {function} callback the callback
     * @returns the filtered array
     */
    filter(callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot filter a non-array value', this.__value);
            return;
        }
        return this.__value.filter(callback);
    }

    /**
     * Maps the array
     * @param {function} callback the callback
     * @returns the mapped array
     */
    map(callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot map a non-array value', this.__value);
            return;
        }
        return this.__value.map(callback);
    }

    /**
     * Reduces the array
     * @param {function} callback the callback
     * @param {*} initialValue the initial value
     * @returns the reduced value
     */
    reduce(callback, initialValue) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot reduce a non-array value', this.__value);
            return;
        }
        return this.__value.reduce(callback, initialValue);
    }

    /**
     * Finds the array
     * @param {function} callback the callback
     * @returns the found value
     */
    find(callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot find in a non-array value', this.__value);
            return;
        }
        return this.__value.find(callback);
    }

    /**
     * Finds the index in the array
     * @param {function} callback the callback
     * @returns the index
     */
    findIndex(callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot find index in a non-array value', this.__value);
            return;
        }
        return this.__value.findIndex(callback);
    }

    /**
     * Returns the length of the array
     * @returns the length
     */
    get length() {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot get length of a non-array value', this.__value);
            return;
        }
        return this.__value.length;
    }

    /**
     * Returns the value at an index
     * @param {number} index the index
     * @returns the value
     */
    at(index) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot get value at index of a non-array value', this.__value);
            return;
        }
        return this.__value[index];
    }

    /**
     * Sets the value at an index
     * @param {number} index the index
     * @param {*} value the value
     */
    setAt(index, value) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot set value at index of a non-array value', this.__value);
            return;
        }
        this.__value[index] = value;
        this.set(this.__value);
    }

    /**
     * Returns the index of a value
     * @param {*} value the value
     * @returns the index
     */
    indexOf(value) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot get index of a non-array value', this.__value);
            return;
        }
        return this.__value.indexOf(value);
    }

    /**
     * Returns the last index of a value
     * @param {*} value the value
     * @returns the index
     */
    lastIndexOf(value) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot get last index of a non-array value', this.__value);
            return;
        }
        return this.__value.lastIndexOf(value);
    }

    /**
     * Clears the array
     * @returns void
     */
    clear() {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot clear a non-array value', this.__value);
            return;
        }
        this.set([]);
    }

    // #endregion

    // #region Dynamic methods

    /**
     * Adds a dynamic rendering depending on the live variables
     * @param  {Function} _callback the live variables and the callback (last argument)
     * @returns {PlacedLive} the placed live
     */
    _(_callback) {
        return dyn(this, _callback);
    }

    /**
     * Returns a either the callback or an anonymous function that returns the value
     * @param {*} value the value
     * @returns the callback
     * @private
     */
    valueCallbacked(value) {
        if(typeof value === 'function') return value;
        return () => value;
    }

    /**
     * Returns a dynamic map of the array
     * @param {function} _callback the callback
     * @returns the dynamic map
     */
    _map(_callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot map a non-array value', this.__value);
            return;
        }
        return dyn(this, (value) => value.map(_callback));
    }

    /**
     * Returns a dynamic if statement
     * @param {function} _trueCallback the true callback
     * @param {function} _falseCallback the false callback
     * @returns the dynamic if statement
     */
    _if(_trueCallback, _falseCallback) {
        return dyn(this, (value) => {
            if(value) return this.valueCallbacked(_trueCallback)(value);
            return this.valueCallbacked(_falseCallback)(value);
        });
    }

    /**
     * Returns a dynamic switch statement
     * @param {object} _cases the cases
     * @returns the dynamic switch statement
     */
    _switch(_cases) {
        return dyn(this, (value) => {
            if(_cases[value] === undefined && _cases.default !== undefined) return this.valueCallbacked(_cases.default)(value);
            return this.valueCallbacked(_cases[value])(value);
        });
    }

    /**
     * Returns a dynamic filter of the array
     * @param {function} _callback the callback
     * @returns the dynamic filter
     */
    _empty(_callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot check if empty a non-array value', this.__value);
            return;
        }
        return dyn(this, (value) => {
            if(value.length === 0) return this.valueCallbacked(_callback)(value);
        });
    }

    _notEmpty(_callback) {
        if(!Array.isArray(this.__value)) {
            dev.error('Cannot check if not empty a non-array value', this.__value);
            return;
        }
        return dyn(this, (value) => {
            if(value.length !== 0) return this.valueCallbacked(_callback)(value);
        });
    }

    // #endregion

    // For objects
    
}

class PlacedLive {

    constructor(...livesAndCallback) {
        let callback = livesAndCallback.pop();
        this.lives = livesAndCallback;
        this.callback = callback;
        this.node = undefined;
        this.selfClassName = undefined;
    }

}

// Live.prototype.valueOf = function (liveId) {
//     return this.get(liveId);
// }

/**
 * Creates a dynamic rendering depending on the live variables
 * @param  {...Live} livesAndCallback the live variables and the callback (last argument)
 * @returns {PlacedLive} the placed live
 */
function dyn(...livesAndCallback) {
    return new PlacedLive(...livesAndCallback);
}

/**
 * Creates a live variable
 * @param {any} value 
 * @returns {Live} the live variable
 */
function live(value) {
    if (value instanceof Live) return new Live(value.get());
    return new Live(value);
}

/**
 * Reacts to live variables when they change (does not execute the callback immediately)
 * @param  {...any} livesAndCallback the live variables and the callback (last argument)
 */
function watchAfter(...livesAndCallback) {
    let callback = livesAndCallback.pop();
    for(let live of livesAndCallback) {
        live.subscribe(async () => {
            let args = livesAndCallback.map((live) => live.get());
            callback(...args);
        });
    }
}

/**
 * Reacts to live variables when they change (also executes the callback immediately)
 * @param  {...any} livesAndCallback the live variables and the callback (last argument)
 */
function watch(...livesAndCallback) {
    let callback = livesAndCallback.pop();
    for(let live of livesAndCallback) {
        live.subscribe(async () => {
            let args = livesAndCallback.map((live) => live.get());
            callback(...args);
        });
    }
    setTimeout(() => callback(...livesAndCallback.map((live) => live.get())), 0);
}
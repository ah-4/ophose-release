class Live {

    static __currentReadingStyleComponent = undefined;
    static __calledLives = {};

    constructor(value) { 
        this.value = value;
        this.__placedLiveTextNodes = [];
        this.__placedLiveNodes = [];
        this.__placedStyleComponents = [];
        this.__callbackListeners = [];
        this.__ruleId = '' + Math.random();
        this.__rules = [];
        this.__rulesDependencies = [];
        this.__updateOnlyIfValueChanges = true;
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
        if(typeof this.value != 'boolean') {
            dev.error('Cannot toggle a non-boolean value', this.value);
            return;
        };
        this.set(!this.value);
        return this.value;
    }

    /**
     * Returns live value and automatically assigns as observer of this
     * @returns the value
     */
    get() {
        if (Live.__currentReadingStyleComponent) {
            let component = Live.__currentReadingStyleComponent;
            this.__placedStyleComponents.push(component);
            component.__lives.push(this);
        }
        for(let id in Live.__calledLives) {
            if(id === this.__ruleId) continue;
            Live.__calledLives[id].push(this);
        }
        return this.value;
    }

    /**
     * Updates the value
     * @param {*} value the value
     */
    set(value) { 
        let oldValue = this.value;
        this.value = value;
        for(let rule of this.__rules) {
            this.value = rule(this.value, oldValue);
        }
        if(this.__updateOnlyIfValueChanges) {
            if(typeof this.value !== 'object') if(this.value === oldValue) return;
        }
        Live.__onValueChange(this, this.value, oldValue);
    }

    /**
     * Updates the value with a callback
     * @param {function} callback the callback (takes the current value as argument and should return the new value)
     * @returns the value
     */
    update(callback) {
        let newValue = callback(this.value);
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
        callback(this.value, this.value);
        for(let live of Live.__calledLives[this.__ruleId]) {
            if(this.__rulesDependencies.indexOf(live) === -1) {
                this.__rulesDependencies.push(live);
                live.subscribe(() => {
                    this.set(this.value);
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
        return this.rule((value) => Math.max(value, min));
    }

    /**
     * Adds maximum value rule to the live variable
     * @param {number} max the maximum value
     * @returns the live variable
     */
    max(max) {
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
        if(Array.isArray(this.value)) {
            this.value.push(value);
            this.set(this.value);
            return;
        }
        this.set(value + this.value);
    }

    /**
     * Removes a value from the current value
     * @param {*} value the value to remove
     */
    remove(value) {
        if(Array.isArray(this.value)) {
            this.value.splice(this.value.indexOf(value), 1);
            this.set(this.value);
            return;
        }
        this.set(this.value - value);
    }
    /**
     * Called when value changes and process needed updates
     * @param {*} liveVar the live variable
     * @param {*} newValue the new value
     * @param {*} oldValue the old value
     */
    static __onValueChange(liveVar, newValue, oldValue) {
        for(let node of liveVar.__placedLiveTextNodes) {
            node.textContent = newValue;
        }
        
        for(let placedLive of liveVar.__placedLiveNodes) {
            let args = placedLive.lives.map((live) => live.get());
            let newNode = ___render___.toNode(placedLive.callback(...args), true);
            if(placedLive.node instanceof DocumentFragment) {
                let node = placedLive.node.oList[0];
                for(let i = 1; i < placedLive.node.oList.length; i++) {
                    placedLive.node.oList[i].remove();
                }
                node.replaceWith(newNode);
                placedLive.node = newNode;
                node.remove();
            } else {
                placedLive.node.replaceWith(newNode);
                placedLive.node = newNode;
            }
            if(placedLive.selfClassName) placedLive.node.classList.add(placedLive.selfClassName);
        }


        for (let component of liveVar.__placedStyleComponents) {
            component.___reloadStyle();
        }
        for (let callback of liveVar.__callbackListeners) {
            callback(newValue, oldValue);
        }
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
            live.addCallbackListener((newValue) => {
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
        obj = Object.assign({}, obj);
        for (let key in obj) {
            if (obj[key] instanceof Live) {
                obj[key] = obj[key].get();
            }
        
            if (typeof obj[key] === 'object') {
                obj[key] = Live.flatten(obj[key]);
            }
        }
        return obj;   
    }
    
}

class PlacedLive {

    constructor(...livesAndCallback) {
        let callback = livesAndCallback.pop();
        this.lives = livesAndCallback;
        this.callback = callback;
        this.node = undefined;
        this.selfClassName = undefined;
        for(let live of this.lives) {
            live.__placedLiveNodes.push(this);
        }
    }

}


Live.prototype.valueOf = function (liveId) {
    return this.get(liveId);
}

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
    if (value instanceof Live) return value;
    return new Live(value);
}

/**
 * Reacts to live variables when they change
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
    (async () => callback(...livesAndCallback.map((live) => live.get())))();
}
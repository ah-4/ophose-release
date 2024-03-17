class Live {

    static __currentReadingStyleComponent = undefined;

    constructor(value) { 
        this.value = value;
        this.__placedLiveTextNodes = [];
        this.__placedLiveNodes = [];
        this.__placedStyleComponents = [];
        this.__callbackListeners = [];
    }

    /**
     * Adds a callback listener
     * @param {function} callback the callback
     */
    addCallbackListener(callback) {
        this.__callbackListeners.push(callback);
    }

    /**
     * Removes a callback listener
     * 
     * @param {function} callback the callback
     * @returns the callback
     */
    removeCallbackListener(callback) {
        this.__callbackListeners.splice(this.__callbackListeners.indexOf(callback), 1);
    }

    /**
     * Toggles the value if it is a boolean
     * 
     * @returns the value or undefined if not a boolean
     */
    toggle() {
        if(typeof this.value != 'boolean') return;
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
        return this.value;
    }

    /**
     * Updates the value
     * @param {*} value the value
     */
    set(value) { 
        let oldValue = this.value;
        this.value = value;
        Live.__onValueChange(this, value, oldValue);
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
            placedLive.node.replaceWith(newNode);
            placedLive.node = newNode;
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
class ___module___ extends ___element___ {

    static __addedModuleStyles = {};

    /**
     * Constructor
     * @param {string} moduleUID the module UID (unique name)
     * @param {___component___} component the listen component
     */
    constructor() {
        super();
        /**
         * @private
         */
        this.__moduleUID = "__ocmodule__" + this.constructor.name.toLowerCase();
        /**
         * @private
         */
        this.__components = [];
        this.__applyStyle();
    }

    /**
     * @returns class name (as DOM element)
     * @private
     */
    _getClassName() {
        return this.__moduleUID;
    }

    /**
     * @returns {HTMLStyleElement} component style node
     * @private
     */
    __getStyleNode() {
        return ___module___.__addedModuleStyles[this._getClassName()];
    }

    /**
     * @private
     */
    __reloadStyle() {
        let styleNode = this.__getStyleNode();
        if (!styleNode) return;
        styleNode.innerText = this.style().replaceAll('%self', '.' + this._getClassName());
    }

    /**
     * Applies style
     * @private
     */
    __applyStyle() {
        if (this.__getStyleNode()) return;
        let styleNode = document.createElement('style');
        ___module___.__addedModuleStyles[this._getClassName()] = styleNode;
        document.head.append(styleNode);
        this.__reloadStyle();
    }

    /**
     * Sets component to the current module instance
     * @param {___component___} the component
     * @private
     */
    addComponent(component) {
        if(!this.__components.includes(component)) return;
        this.__components.push(component);
        this.onComponentAdded(component);
    }

    /**
     * @returns {___component___[]} components of the module
     */
    getComponents() {
        return this.__components;
    }

    /**
     * Removes component from the current module instance
     * @param {___component___} the component
     */
    removeComponent(component) {
        // Remove style node if no components associated
        if (this.__components.length == 0) {
            this.__getStyleNode().remove();
        }
        this.onComponentRemoved(component);
    }

    /**
     * This function is called when a component is added to the module
     * @param {___component___} component the component
     * @abstract
     */
    onComponentAdded(component) {

    }

    /**
     * This function is called when a component is removed from the module
     * @param {___component___} component the component
     * @abstract
     */
    onComponentRemoved(component) {

    }

    /**
     * This function is called when an element implementing this
     * module is placed and rendered.
     * @param {___component___} component the component
     * @param {HTMLElement} element DOM element
     */
    onPlace(component, element) {

    }

    /**
     * This function is called when an element implementing this
     * module is removed.
     * @param {___component___} component the component
     * @param {HTMLElement} element DOM element
     */
    onRemove(component, element) {

    }

    /**
     * @private
     */
    __onRemove(component, element) {
        this.__components.splice(this.__components.indexOf(component), 1);
        this.onRemove(component, element);
    }

}
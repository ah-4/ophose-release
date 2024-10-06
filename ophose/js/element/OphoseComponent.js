/**
 * Represents an ophose component.
 */
class ___component___ extends ___element___ {

    static __allComponents = {};
    static __SCREENS_SIZES = {
        sm: 576,
        md: 768,
        lg: 992,
        xl: 1200
    }

    /**
     * Returns the component ID
     * @param {*} componentId the component id
     * @returns {___component___} component matching id
     */
    static getComponent(componentId) {
        return ___component___.__allComponents[componentId];
    }

    constructor(props = {}) {
        super();
        this.props = (props && typeof props == "object" && props) || {};
        if(this.props.c) this.props.children = this.props.c;
        
        /**
         * @private
         */
        this.__propsOn

        /**
         * @private
         */
        this.__styleNode = undefined;

        /**
         * @private
         */
        this.__node = undefined;

        /**
         * @private
         */
        this.__loadingContext = null;

        /**
         * @private
         */
        this.compUID = this.constructor.name.toLowerCase();

        /**
         * @private
         */
        this.__componentUniqueClassName = "__oc_" + this.compUID;
        this.__componentUniqueClassName = this.__componentUniqueClassName.prettyHashCode();

        /**
         * @private
         */
        this.__componentUniqueId = this.__componentUniqueClassName + (Object.keys(___component___.__allComponents).length + 1);

        ___component___.__allComponents[this.__componentUniqueId] = this;

        /**
         * @private
         */
        this.__myModules = [];

        /**
         * @private
         */
        this.__lives = [];
    }

    /**
     * Sets component node
     * @param {HTMLElement} node the node
     * @private
     */
    __setNode(node) {
        this.__node = node;
    }

    /**
     * @returns component unique id (unique for every component)
     * @private
     */
    __getComponentUniqueId() {
        return this.__componentUniqueId;
    }

    // Style

    /**
     * @returns {object} Returns the additional styles as {screen: style} (for example: {
     *      md: `
     *          %self {
     *              background-color: red;
     *         }
     *      `
     * })
     */
    styles() {
        return null;
    }

     /**
     * @returns {HTMLStyleElement} component style node
     * @private
     */
     __getStyleNode() {
        return this.__styleNode;
    }

    /**
     * Creates style node
     * @returns {HTMLStyleElement} style node (or undefined if already exists)
     * @private
     */
    __createStyleNode() {
        if(this.__getStyleNode()) return undefined;
        let styleNode = document.createElement('style');
        document.head.append(styleNode);
        this.__styleNode = styleNode;
        return styleNode;
    }

    /**
     * Reloads component style
     * @private
     */
    ___reloadStyle() {
        let style = this.style().replaceAll('%self', '.' + this.__getComponentUniqueId());
        let additionalStyles = this.styles();
        if(additionalStyles) {
            for(let screen in additionalStyles) {
                if(!___component___.__SCREENS_SIZES[screen]) {
                    dev.error('Invalid screen size: ' + screen);
                    continue;
                }
                let additionalStyle = additionalStyles[screen].replaceAll('%self', '.' + this.__getComponentUniqueId());
                style += '\n@media screen and (max-width: ' + ___component___.__SCREENS_SIZES[screen] + 'px) {\n' + additionalStyle + '\n}\n';
            }
        }
        let styleNode = this.__getStyleNode();
        styleNode.innerText = style;
    }

    /**
     * Applies style
     * @private
     */
    __applyStyle() {
        if (this.__getStyleNode()) return;
        this.__createStyleNode();
        Live.__currentReadingStyleComponent = this;
        this.___reloadStyle();
        Live.__currentReadingStyleComponent = undefined;
    }

    /**
     * Moves html properties to the child with the given name (set undefined to remove it)
     * @param string name the name
     */
    propsOn(name) {
        this.__propsOn = name;
    }

    /**
     * @returns {HTMLElement} component node
     */
    getNode() {
        return this.__node;
    }

    /**
     * Return rendered node
     * @param {HTMLElement} node the created node
     * @returns {HTMLElement} node
     * @private
     */
    __place(node = undefined) {

        if(node instanceof DocumentFragment) {
            for(let n of node.oList) {
                n.classList.add(this.__getComponentUniqueId());
            }
        }else{
            node.classList.add(this.__getComponentUniqueId());
        }

        this.__setNode(node);
        this.__applyStyle();
    }

    /**
     * Abstract method to render HTML
     * @returns HTML
     */
    render() {

    }

    /**
     * Removes component from the DOM & Ophose instances
     */
    remove() {
        this.__processRemove();
        this.__node.remove();
        ___render___.__placedOphoseInstances.splice(___render___.__placedOphoseInstances.indexOf(this), 1);
    }

    /**
     * Append child to component
     * 
     * @param {object} child the child
     */
    appendChild(child) {
        this.__node.append(___render___.toNode(child));
    }

    /**
     * Called when component will be removed from the DOM
     * @private
     */
    __processRemove() {
        this.onRemove(this.__node);
        for(let module of this.__myModules) {
            module.__onRemove(this, this.__node);
        }
        if(this.__styleNode) {
            this.__styleNode.remove();
            this.__styleNode = undefined;
        }
        ___component___.__allComponents[this.__componentUniqueId] = undefined;
    }

    // Relatives

    /**
     * Returns the first parent component of the given type
     * @param {*} componentType the component type
     * @returns {Ophose.Component} the component or null if not found
     */
    findFirstParentComponentOfType(componentType) {
        let parent = this.__node.parentElement;
        while(parent) {
            if(parent.ophoseInstance && parent.ophoseInstance instanceof componentType) {
                return parent.ophoseInstance;
            }
            parent = parent.parentElement;
        }
        return null;
    }

    // Lifecycle

    /**
     * Called when component is added to the DOM
     * @param {HTMLElement} element the element
     */
    onPlace(element) {

    }

    /**
     * Called when component will be removed from the DOM
     * @param {HTMLElement} element the element
     */
    onRemove(element) {

    }

    // MODULE

    /**
     * Add module to the current component
     * @param {___module___} module the module
     */
    addModule(module) {
        this.__myModules.push(module);
        module.addComponent(this);
    }

}
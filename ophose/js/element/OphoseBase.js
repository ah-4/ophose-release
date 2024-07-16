class ___base___ extends ___component___ {

    static registered = {};

    constructor(props) {
        super(props);
    }

    /**
     * Register a new custom element
     * @param {*} tagName the name of the custom element
     * @param {*} clsComponent the class of the component
     */
    register(tagName, clsComponent) {
        // Check if the tag name is not a native HTML tag like div, span, etc.
        let element = document.createElement(tagName);
        if(!(element instanceof HTMLUnknownElement) && element.constructor !== HTMLElement){
            dev.error(`The tag name ${tagName} is not a valid custom element.\nThe element is: ${element}`);
            return;
        }
        ___base___.registered[tagName] = clsComponent;
    }

}
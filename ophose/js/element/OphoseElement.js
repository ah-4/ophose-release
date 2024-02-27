class ___element___ {

    constructor() {

    }

    /**
     * Abstract method to define style of the element
     */
    style() {
        return '';
    }

    /**
     * Returns page base
     * @returns {___base___} base
     */
    getBase() {
        return ___app___.__base;
    }
    
}
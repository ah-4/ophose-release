/**
 * Represents an ophose page
 */
class ___page___ extends ___component___ {

    /**
     * Page constructor
     * @param {dict} urlQueries url queries if any (ex: pageId, productId...)
     */
    constructor(urlQueries) {
        super();
        this.urlQueries = urlQueries;
        this.__redirected = false;
    }

    /**
     * This method is called once the page is created
     * (only once)
     */
    onCreate() {

    }

    /**
     * This method is called when the page is left
     * through route.go(other_page)
     */
    onLeave() {

    }

    /**
     * This method is called when the page is loaded
     * (every time)
     */
    onLoad() {
            
    }

    onPlace(element) {
        super.onPlace(element);
        ___event___.callEvent("onPageLoaded", app.CURRENT_URL);
    }

    redirect(url) {
        if (this.__redirected) {
            dev.error("This page has already been redirected.");
            return;
        }
        this.__redirected = url;
    }
    
}
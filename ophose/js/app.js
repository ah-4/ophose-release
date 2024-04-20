var page = undefined;

class ___app___ {

    static __base = undefined;
    static __$nodePage;
    static __$baseAppNode;
    static __$pageStyle;
    static __$baseStyle;
    static __histories = [];
    static __loadedPages = {};
    static __currentURL = null;
    static CURRENT_URL = null;
    static __lastShared = undefined;

    /**
     * Loads base component it is not loaded
     */
    static __loadBase() {
        if (___app___.__base) return;
        // Loading Base
        ___app___.__base = new Base({
            children: {
                _: 'main',
                id: 'oapp'
            }
        });
        let baseOph = ___app___.__base;
        let renderedNode = ___render___.toNode(baseOph, true);
        document.body.appendChild(renderedNode);
        ___app___.__$baseAppNode = document.getElementById("oapp");
        ___render___.__placedOphoseInstances = [];
        // Loading style node
        ___app___.__$pageStyle = document.createElement("style");
        document.head.appendChild(___app___.__$pageStyle);
        // Init
    }

    /**
     * Loads ophose page at requested URL
     * @param {*} requestUrl requested URL
     */
    static __loadAt(requestUrl) {

        /**
         * Returns the URL with pages prefix
         * @param {string} url the URL
         * @returns the URL with pages prefix
         */
        let prepareUrlWithPagesPrefix = (url) => {
            if (url.startsWith('/')) url = url.substring(1, url.length);
            if (url.endsWith('/')) url = url.substring(0, url.length - 1);
            return '/pages/' + url;
        }

        let scrollToUrlId = (ms) => {
            setTimeout(() => {
                if(!window.location.hash) {
                    window.scrollTo(0, 0);
                    return;
                };
                let id = window.location.hash.substring(1, window.location.hash.length);
                let element = document.getElementById(id);
                if (element) {
                    element.scrollIntoView({
                        behavior: 'smooth'
                    });
                }else{
                    window.scrollTo(0, 0);
                }
            }, ms);
        }

        ___app___.__loadBase();
        ___event___.callEvent("onPageLoad", requestUrl);

        ___app___.CURRENT_URL = requestUrl;
        requestUrl = prepareUrlWithPagesPrefix(requestUrl);

        let urlAndQuery = requestUrl.split("?");
        let urlFullPath = urlAndQuery[0].split('#')[0];
        let urlPath = urlAndQuery[0].split('/');
        if (urlPath[urlPath.length - 1] == "") {
            urlPath.pop(urlPath.length - 1);
        }

        if (requestUrl == "/pages/") {
            urlFullPath = "/pages/index";
        }
        
        if(___app___.__currentURL == urlFullPath) {
            scrollToUrlId(0);
            return;
        } 
        ___app___.__currentURL = urlFullPath;

        // Returns fixed JSON response
        let getUrlQueries = () => {
            let result;
            $.ajax({
                type: 'POST',
                url: '/@query/',
                async: false,
                data: {url: urlFullPath},
                success: function (r) {
                    result = r;
                }
            });
            return result;
        }
        let jsonResponse = getUrlQueries();
        

        let urlExists = jsonResponse["valid"];
        let urlRequest = jsonResponse["path"];

        // Define URL queries
        let urlQueries = {};
        urlQueries.query = jsonResponse["variables"];
        if(urlAndQuery.length == 2) {
            urlQueries.get = {};
            let urlQueriesArray = urlAndQuery[1].split('#')[0].split("&");
            for (let urlQuery of urlQueriesArray) {
                let urlQueryArray = urlQuery.split("=");
                urlQueries.get[urlQueryArray[0]] = urlQueryArray[1];
            }
        }

        if (!urlExists && urlFullPath != "error") {
            ___app___.__loadAt("error");
            return;
        }
        

        let Page = undefined;

        let loadPage = (PageClass) => {

            if(!___app___.__loadedPages[urlRequest]) ___app___.__loadedPages[urlRequest] = {};
            ___app___.__loadedPages[urlRequest]["pageClass"] = PageClass;

            let loadedPage = new PageClass(urlQueries);
            for (let ophoseInstance of ___render___.__placedOphoseInstances) {
                let node = ophoseInstance.__node;
                if(!___app___.__$baseAppNode.contains(node)) continue;
                ophoseInstance.__processRemove();
            }
            ___render___.__placedOphoseInstances = [];

            loadedPage.onCreate();

            // Check if page has been redirected
            if (loadedPage.__redirected) {
                ___app___.__loadAt(loadedPage.__redirected);
                return;
            }

            // Load content
            let pageNode = ___render___.toNode(loadedPage, true);
            loadedPage.onLoad();

            ___app___.__$baseAppNode.replaceWith(pageNode);
            ___app___.__$baseAppNode = pageNode;
            loadedPage.__applyStyle();

            ___app___.__pageInstance = loadedPage;

            loadedPage.__setNode(pageNode);
            loadedPage.onPlace(pageNode);
            page = loadedPage;
            scrollToUrlId(100);
            ___event___.callEvent("onPageLoaded", requestUrl);
        }

        if (urlFullPath != "error" && ___app___.__loadedPages[urlRequest]) {
            let pageClass = ___app___.__loadedPages[urlRequest]['pageClass'];
            loadPage(pageClass);
            return;
        }

        // Page loading
        ___script___.run(urlRequest, false,
            () => {
                Page = ___app___.__getShared();
                loadPage(Page);
            },
            () => {
                if (urlFullPath == "error") return;
                ___app___.__loadAt("error");
            }
        );
        
    }

    /**
     * Sets application title
     * @param {string} newTitle the new title
     */
    static setTitle(newTitle) {
        document.title = newTitle;
    }

    /**
     * Used to export an object
     * @param {object} object the object
     */
    static share(object) {
        ___app___.__lastShared = object;
    }

    /**
     * Used to get the last shareed object and reset it
     * @returns {object} the last shareed object
     */
    static __getShared() {
        let e = ___app___.__lastShared;
        ___app___.__lastShared = undefined;
        return e;
    }

}

const app = ___app___;
const oshare = ___app___.share; 
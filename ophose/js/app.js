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
        let renderedNode = ___render___.toNode(baseOph);
        document.body.appendChild(renderedNode);
        ___app___.__$baseAppNode = document.getElementById("oapp");
        ___render___.__placedOphoseInstances = [];
        // Loading style node
        ___app___.__$pageStyle = document.createElement("style");
        document.head.appendChild(___app___.__$pageStyle);
        // Init
    }

    /**
     * Goes to a page
     * @param {string} url the page URL
     * @returns {Promise} the promise
     */
    static async __go(url) {
        ___app___.__loadBase();
        url = url.split("#")[0].split("?")[0];
        if(url == ___app___.__currentURL) return null;
        return await fetch('/@resolve/', {
            method: 'POST',
            body: JSON.stringify({url})
        })
        .then(r => r.json())
        .then(async r =>{
            let js = r.js;
            let query = r.query;
            let url = r.url;
            let data = r.data;
            let get = window.location.search.substring(1).split('&').map(e => e.split('=')).reduce((a, b) => {a[b[0]] = b[1]; return a}, {});

            if(!___app___.__loadedPages[js]) {
                let script = await fetch('/pages/' + js).then(r => r.text());
                eval(`${script}`);
                ___app___.__loadedPages[js] = {cls: ___app___.__getShared()};
            }

            let c = ___app___.__loadedPages[js]['cls'];
            let page = new c({
                query,
                url,
                data,
                get
            });
            let node = ___render___.toNode(page);

            if(___app___.__$baseAppNode instanceof DocumentFragment) {
                let list = ___app___.__$baseAppNode.oList;
                for(let i = 1; i < list.length; i++) {
                    list[i].remove();
                }
                list[0].replaceWith(node);
            } else {
                ___app___.__$baseAppNode.replaceWith(node);
            }

            ___app___.__currentURL = url;
            ___app___.__$baseAppNode = node;
            page.__applyStyle();

            return r;
        })
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

    /**
     * Returns the base component
     * 
     * @returns {Base} the base component
     */
    static getBase() {
        return ___app___.__base;
    }

}

const app = ___app___;
const oshare = ___app___.share; 
class ___route___ {

    /**
     * To go to another page (without reloading base)
     * @param {page} requestUrl requested URL
     */
    static go(requestUrl) {
        if (requestUrl === undefined || requestUrl === null) {
            return;
        }
        if(/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)/.test(requestUrl)) {
            window.location = requestUrl;
            return;
        }
        ___app___.__loadAt(requestUrl);
        if (requestUrl != ___app___.__currentURL) {
            window.history.pushState(requestUrl, '', requestUrl);
        }
    }

}

const route = ___route___;
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
        if(requestUrl[0] == '#') {
            document.querySelector(requestUrl)?.scrollIntoView({behavior: 'smooth'});
            window.history.pushState(requestUrl, '', requestUrl);
        }
        ___app___.__go(requestUrl).then((r) => {
            // Return if history is already the same
            if(!r) return;
            if (r.url === window.location.pathname) return;
            window.history.pushState(r.url, '', r.url);
        });
    }

}

const route = ___route___;
class ___import___ {

    static __imported = [];
    static __lastExport = undefined;

    static __fixedPath(path) {
        if(path.startsWith("@/")) path = path.replace("@/", "ext/");
        return path.trim().replaceAll("//", "/");
    }

    static __use(path) {
        if(___import___.__imported.includes(path)) return;
        if(__OPH_APP_BUILD__) return;
        ___import___.__imported.push(path);
        ___script___.run(path);
    }

    /**
     * Imports once the class component
     * @param {string} path the path
     */
    static useComponent(path) {
        ___import___.__use("/@component/" + ___import___.__fixedPath(path) + '.js');
    }

    /**
     * Imports once the class module
     * @param {string} path the path
     */
    static useModule(path) {
        ___import___.__use("/@module/" + ___import___.__fixedPath(path) + '.js');
    }

    /**
     * Imports once the environment script or a component
     * @param {string} path the path
     */
    static useEnvironment(path) {
        ___import___.__use(___import___.__fixedPath("/@envjs/" + path));
    }

    /**
     * Imports once the CSS
     * @param {string} path the path
     */
    static importCss(path) {
        let link = document.createElement("link");
        link.rel = "stylesheet";
        link.type = "text/css";
        link.href = path;
        document.head.appendChild(link);
    }

    /**
     * Imports once the script
     * @param {string} path the path
     */
    static importScript(path) {
        $.ajax({
            url: path,
            dataType: "script",
            async: false
        });
    }

}

const importCss = ___import___.importCss;
const importScript = ___import___.importScript;

const oimpc = ___import___.useComponent;
const oimpm = ___import___.useModule;
const oimpe = ___import___.useEnvironment;
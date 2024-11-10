class ___plugin___ {

    static plugins = {
        plugins: [],
        use: {
            render: []
        }
    };

    constructor(name) {
        dev.log(`Plugin ${name} loading.`);
    }

    useRender(callback) {
        ___plugin___.plugins.use.render.push(callback);
    }

}
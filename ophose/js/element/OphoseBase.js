class ___base___ extends ___component___ {

    constructor(props) {
        super(props);
    }

    usePlugin(plugin) {
        if(typeof plugin === 'function' && plugin.prototype instanceof ___plugin___) {
            if(___plugin___.plugins.plugins.indexOf(plugin) === -1) {
                ___plugin___.plugins.plugins.push(plugin);
                new plugin();
                dev.log(`Plugin ${plugin.name} loaded.`);
            } else {
                dev.error(`Plugin ${plugin.name} already loaded.`);
            }
            return;
        }
        dev.error(`Invalid plugin ${plugin}. Plugin must be a class.`);
    }



}
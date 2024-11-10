/**
 * Class representing ophose environment
 */
class ___env___ {

    /**
     * Sends asychronous request (POST) to the environment
     * and return JSON parsed result
     * @param {string} endpoint the environment and its endpoint (for example: /myEnv/myEndpoint)
     * @param {Array} data post data
     */
    static async post(endpoint, data = null) {

        let jsonResult = null;
        let error = false;
        if(data instanceof Live) {
            data = data.value;
        }
        let options = {
            method: 'POST',
            headers: {},
            useDirectives: true,
            ...(data && data.options) ?? {}
        }
        if(data && data.options) data.options = undefined;
        if(options.toFormData) {
            let formData = new FormData();
            for(let key in data) {
                formData.append(key, data[key]);
            }
            data = formData;
        }
        if(!(data instanceof FormData)) {
            if(typeof data === 'object') {
                options.headers['Content-Type'] = 'application/json';
                data = JSON.stringify(Live.flatten(data));
            } else if(typeof data === 'string' || typeof data === 'number' || typeof data === 'boolean' || data === null) {
                if(data === null) data = '';
                options.headers['Content-Type'] = 'text/plain';
            }
        }

        await $.ajax({
            type: options.method,
            url: (endpoint.startsWith('/@/') || endpoint.startsWith('/api/' || endpoint.startsWith('/@api/') || endpoint.startsWith('/@env/'))) ? endpoint : '/@/' + endpoint,
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: (xhr) => {
                let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                xhr.setRequestHeader('X-Csrf-Token', token);
                for(let header in options.headers) {
                    xhr.setRequestHeader(header, options.headers[header]);
                }
            },
            success: (result) => {
                jsonResult = result;
            },
            error: (result) => {
                error = true;
                try {
                    jsonResult = JSON.parse(result.responseText);
                } catch (e) {
                    jsonResult = result.responseText;
                }
            }
        });

        if (error) {
            throw jsonResult;
        }

        if(options.useDirectives && jsonResult?.ophose_encoded_directives) {
            for(let directive of jsonResult.ophose_encoded_directives) {
                switch(directive.type) {
                    case 'redirect':
                        route.go(directive.data);
                        break;
                }
            }
            return;
        }
        
        return jsonResult;
    }

    /**
     * Sends files to an environment request
     * @param {string} env the environment path
     * @param {string} request the rest request
     * @param {string} name the file(s) name
     * @param {boolean} multiple if user can send multiple file
     */
    static async sendFiles(env, request, filename, callbackSuccess, callbackError, multiple = false, acceptTypes = "*") {
        let url = ___env___.constructURL(env, request);

        let __form = document.createElement("form");
        __form.enctype = "multipart/form-data";

        let __fileInput = document.createElement("input");
        __fileInput.type = "file";
        __fileInput.accept = acceptTypes;
        if (multiple) {
            __fileInput.multiple = true;
        }
        __fileInput.id = filename;
        __fileInput.name = filename;

        __form.appendChild(__fileInput);

        __fileInput.click();

        let dataReceived = null;

        __fileInput.onchange = e => {
            let form_data = new FormData(__form);
            $.ajax({
                url: url, // <-- point to server-side PHP script 
                dataType: 'json',  // <-- what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'POST',
                method: 'POST',
                success: (r) => {
                    callbackSuccess(r) // <-- display response from the PHP script, if any
                },
                error: (r) => {
                    callbackError(r);
                }
            });
        };


    }

}

/**
 * Class representing an environment entity
 */
class ___env_entity___ {

    constructor(id, data) {
        this.__id = id;
        this.data = data;
    }

    /**
     * Returns entity id
     * @returns {*} id
     */
    getId() {
        return this.__id;
    }

    /**
     * Method called when entity is updated
     * @param {*} data the new data
     * @abstract
     */
    update(data) {

    }

}

/**
 * Class representing an environment entity manager
 */
class ___env_entity_manager___ {

    /**
     * Environment constructor
     * @param {___env_entity___}
     * @param {boolean} saveEntitiesInCache if environment should saves 
     */
    constructor(EntityClass, saveEntitiesInCache = true) {
        this.__EntityClass = EntityClass;
        this.__entities = {};
        this.__saveEntitiesInCache = saveEntitiesInCache;
    }

    /**
     * Returns entity (null if not overriden)
     * (Note that if this function isn't overriden, it'll
     * return entity from array, or entity created from
     * createEntity(id))
     * @param {*} id the entity id
     * @returns {___env_entity___} entity
     */
    async getEntity(id) {
        if (this.saveEntitiesInCache && this.__entities[id]) {
            return this.__entities[id];
        }
        return await this.__generateEntity(id);
    }

    /**
     * Generates entity then returns it
     * @param {*} id the entity id
     * @returns entity
     */
    async __generateEntity(id) {
        let entity = await this.createEntity(id);
        if (entity === undefined) {
            return undefined;
        }
        if (this.saveEntitiesInCache) {
            this.__entities[id] = entity;
        }
        return entity;
    }

    /**
     * This method is called when an entity is needed to
     * be created
     * @param {*} id the entity id
     * @returns created entity (null by default)
     */
    async createEntity(id) {
        return null;
    }

    /**
     * Saves entity in cache with data
     * @param {*} id the entity id
     * @param {*} data the entity data
     */
    saveEntity(id, data) {
        if(!this.__saveEntitiesInCache) return;
        if(this.__entities[id]) {
            this.__entities[id].data = data;
            this.__entities[id].update(data);
            return;
        }
        this.__entities[id] = new this.__EntityClass(id, data);
    }

    /**
     * Cleans all created entities
     */
    cleanEntities() {
        this.__entities = {};
    }

}

const oenv = ___env___.post;
const oenvSendFiles = ___env___.sendFiles;
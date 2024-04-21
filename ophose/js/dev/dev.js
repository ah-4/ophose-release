class dev {

    static errors = new Live([]);

    /**
     * Add an error to the list of errors
     * @param {*} message 
     */
    static error(message) {
        if(project.productionMode) return;
        let error = new Error(message);
        let stack = error.stack.split("\n");
        for(let i = 0; i < stack.length; i++) {
            if(stack[i].includes("at dev.error")) {
                stack.splice(i, 1);
                break;
            }
        }
        error.stack = stack.join("\n");
        console.error(error);
        dev.errors.add(error);
    }

    static init() {
        if(project.productionMode) return;

        let _error = class extends Ophose.Component {

            constructor(props) {
                super(props);

                this.showed = new Live(false);
                this.selectedError = new Live(0);
            }

            style() {
                return /* css */`
                    %self {
                        position: fixed;
                        bottom: 2rem;
                        left: 2rem;
                        background-color: #c44;
                        color: #fff;
                        border-radius: 5px;
                        box-shadow: 0 0 10px rgba(0,0,0,0.2);
                        z-index: 10000;
                        font-family: Arial, sans-serif;
                    }

                    %self .error_popup {
                        user-select: none;
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                    }

                    %self .error_popup span {
                        padding: 1rem;
                        transition: 0.3s;
                    }

                    %self .error_popup span:hover {
                        padding: 1rem 1.25rem;
                    }

                    %self .close_btn {
                        top: 0;
                        right: 0;
                        padding: 0.25rem 1rem;
                        font-size: 0.75rem;
                        border-radius: 5px;
                        background-color: #a44;
                        transition: 0.3s;
                        margin-right: 1rem;
                    }

                    %self .close_btn:hover {
                        background-color: #844;
                    }

                    /* Error container */

                    %self .error_container {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0,0,0,0.5);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 10001;
                        color: #333;
                    }

                    %self .error_container_inner {
                        background-color: #f1f1f1;
                        padding: 1rem;
                        border-radius: 1rem;
                        box-shadow: 0 0 10px rgba(0,0,0,0.2);
                        max-width: 90%;
                        max-height: 90%;
                        overflow: auto;
                        position: relative;
                        width: 60%;
                        height: 70%;
                        min-width: 600px;
                        min-height: 400px;
                    }

                    %self .error_container_close {
                        position: absolute;
                        top: 1rem;
                        right: 1rem;
                        font-size: 0.75rem;
                        cursor: pointer;
                    }

                    %self .error_list {
                        display: flex;
                        gap: 1rem;
                        margin: 1rem 0;
                        font-size: 0.8rem;
                        align-items: center;
                        font-weight: bold;
                    }

                    %self .error_list a {
                        padding: 0.5rem 1rem;
                        border-radius: 5px;
                        background-color: #999;
                        color: #fff;
                        cursor: pointer;
                        transition: 0.3s;
                    }

                    %self .error_list a:hover {
                        background-color: #888;
                    }

                    %self .error_list p {
                        color: #666;
                    }

                    %self h2 {
                        margin-bottom: 1rem;
                    }

                    %self h3 {
                        color: #c44;
                        font-size: 1rem;
                        margin: 1rem 0;
                    }

                    %self pre {
                        white-space: pre-wrap;
                        font-size: 0.75rem;
                        background-color: #ddd;
                        padding: 1rem;
                        border-radius: 5px;
                        overflow: auto;
                    }

                    %self .stack_trace {
                        font-size: 0.75rem;
                        color: #666;
                        margin: 1rem 0;
                    }
                `
            }

            render() {
                return _('div',
                    dyn(dev.errors, (errors) => {
                        if (errors.length == 0) return;
                        return _('p', {className: 'error_popup'}, 
                            _('span', {onclick: () => this.showed.set(true)}, errors.length + ' error(s) occurred'),
                            _('a', {className: 'close_btn', onclick: () => {
                                dev.errors.set([]);
                            }}, 'Clear')
                        )
                    }),
                    dyn(dev.errors, this.showed, this.selectedError, (errors, showed, selectedError) => {
                        if(errors.length == 0 || !showed) return;
                        let error = errors[selectedError];
                        return _('div', {className: 'error_container'},
                            _('div', {className: 'error_container_inner'},
                                _('a', {className: 'error_container_close', onclick: () => this.showed.set(false)}, 'Close'),
                                _('h2', 'An error occurred'),
                                _('div', {className: 'error_list'},
                                    _('a', {onclick: () => {
                                        this.selectedError.update((v) => v == 0 ? 0 : v - 1)
                                    }}, 'Previous'),
                                    _('a', {onclick: () => {
                                        this.selectedError.update((v) => v == errors.length - 1 ? errors.length - 1 : v + 1)
                                    }}, 'Next'),
                                    _('p', 'You are currently viewing error ' + (selectedError + 1) + '/' + errors.length),
                                ),
                                _('h3', error.message),
                                _('p', {className: 'stack_trace'}, 'Stack trace:'),
                                _('pre', error.stack)
                            )
                        )
                    })
                )
            }

        };

        Ophose.Event.addListener("onPageLoaded", () => {
            app.getBase().appendChild(new _error());
        })
    }
}

dev.init();
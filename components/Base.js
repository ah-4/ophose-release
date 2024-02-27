class Base extends Ophose.Base {

    constructor(props) {
        super(props);
        app.setTitle("Your Ophose App");
    }

    style() {
        let theme = Live.local("theme", "dark").get();
        return /* css */`
            :root {
                --font-family: Arial, sans-serif;
                --bg-color: ${theme == "dark" ? "#111" : "#f0f0f0"};
                --font-color: ${theme == "dark" ? "#f0f0f0" : "#666"};
                --font-color-secondary: ${theme == "dark" ? "#aaa" : "#999"};
                --link-color: ${theme == "dark" ? "#88a" : "#88b"};
                --comp-color: ${theme == "dark" ? "#222" : "#ddd"};
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: var(--font-family);
                background-color: var(--bg-color);
                color: var(--font-color);
            }
        `
    }

    render() {
        return {
            _: 'div', c: [
                {
                    _: 'main',
                    id: 'page',
                    children: this.props.children
                }
            ]
        }
    }

}
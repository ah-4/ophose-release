class Base extends Ophose.Base {

    constructor(props) {
        super(props);
        app.setTitle("Your Ophose App");
    }

    style() {
        let theme = Live.local("theme", "dark");
        return /* css */`
            :root {
                --font-family: Arial, sans-serif;
                --bg-color: ${theme.value == "dark" ? "#111" : "#f0f0f0"};
                --font-color: ${theme.value == "dark" ? "#f0f0f0" : "#666"};
                --font-color-secondary: ${theme.value == "dark" ? "#aaa" : "#999"};
                --link-color: ${theme.value == "dark" ? "#88a" : "#88b"};
                --comp-color: ${theme.value == "dark" ? "#222" : "#ddd"};
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
        return _('div',
            this.props.children
        )
    }

}
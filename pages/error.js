class SimpleErrorDisplay extends Ophose.Component {

    constructor(props) {
        super(props);

        app.setTitle('Error | Ophose');
    }

    style() {
        return /* css */`
        %self {
            height: fit-content;
            background-color: var(--ophose-fav-color);
            border-radius: 1em;
            text-align: center;
        }
        `;
    }

    render() {
        return {_: 'div', children: [
            _('h1', '404'),
            _('p', 'This page may not exist')
        ]
        }
    }

}

class PageError extends Ophose.Page {

    constructor() {
        super();
    }

    onLoad() {
        ___app___.setTitle('404 - This page may not exist');
    }

    style() {
        return /* css */`
        .page_error {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        `
    }

    render() {
        return {
            _: 'div',
            className: 'page_error',
            children: new SimpleErrorDisplay()
        }
    }

}

oshare(PageError);
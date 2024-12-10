class SimpleErrorDisplay extends Ophose.Component {

    constructor(props) {
        super(props);
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
        return _div(
            _h1('404'),
            _p('This page may not exist')
        )
    }

}

class PageError extends Ophose.Page {

    constructor() {
        super();

        app.setTitle('404 - This page may not exist');
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
        return _div({className: 'page_error'},
            new SimpleErrorDisplay()
        )
    }

}

oshare(PageError);
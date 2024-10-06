class Wrapper extends Ophose.Component {

    constructor(props) {
        super(props);
    }

    style() {
        return /* css */`
            %self {
                margin: 0 auto;
                max-width: 1000px;
                width: 80%;
                position: relative;
            }
        `
    }

    styles() {
        return {
            md: /* css */`
                %self {
                    padding: 0 1rem;
                    width: 95%;
                }
            `,
        }
    }

    render() {
        return _('div',
            this.props.children
        )
    }
}
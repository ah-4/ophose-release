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
                    padding: 0 20px;
                    width: 95%;
                }
            `,
        }
    }

    render() {
        return {_: 'div', children: this.props.children}
    }
}
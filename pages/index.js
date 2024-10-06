oimpc("Wrapper");

class PageIndex extends Ophose.Page {

    constructor(urlQueries) {
        super(urlQueries);
    }

    style() {
        return /* css */`
            %self {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100vh;
                gap: 20px;
            }

            %self h1 {
                font-size: 2.5rem;
                text-align: center;
            }

            %self img {
                width: 200px;
                height: 200px;
            }

            %self .links {
                display: flex;
                gap: 2rem;
                align-items: center;
            }

            %self .links a {
                color: var(--link-color);
                text-decoration: none;
                font-size: 1rem;
                transition: 0.3s;
                cursor: pointer;
            }

            %self .links a:hover {
                color: var(--font-color);
                font-size: 1.125rem;
            }

            %self input {
                width: 100%;
                padding: 1rem;
                font-size: 1.5rem;
                border: none;
                border-radius: 1rem;
                background-color: var(--comp-color);
                color: var(--font-color);
                transition: 0.3s;
            }

            %self #ah4 {
                color: inherit;
                transition: 0.3s;
                text-decoration: none;
            }

            %self #ah4:hover {
                color: var(--link-color);
            }

            %self .description {
                font-size: 0.75rem;
                color: var(--font-color-secondary);
            }
        `
    }

    styles() {
        return {
            md: /* css */`
                %self h1 {
                    font-size: 2rem;
                }

                %self .links {
                    flex-direction: column;
                    gap: 1rem;
                }
            `
        }
    }

    render() {
        let input = live('AH4');

        return new Wrapper({children: [
            _('img', {src: '/ophose.png', alt: 'placeholder', draggable: false}),
            _('h1', 'Welcome ', _('i', input), ', to your application'),
            _('div', {className: 'links'},
                _('a', {href: 'https://ophose.ah4.fr/tutorials'}, 'Getting started'),
                _('a', {href: 'https://ophose.ah4.fr/docs'}, 'Documentation'),
                _('a', {href: 'https://ophose.ah4.fr/store'}, 'Resources'),
                _('a', {onclick: () => {
                    let theme = Live.local("theme");
                    theme.set(theme.value == "dark" ? "light" : "dark");
                }}, 'Toggle theme')
            ),
            _('input', {
                placeholder: 'Type something...',
                watch: input
            }),
            _('p', {className: 'description'},
                'Ophose (by ',
                _('a', {id: 'ah4', href: 'https://ah4.fr/'}, 'AH4'),
                ') is a simple and powerful framework to create web applications. It is based on the concept of components and is designed to be easy to use and to learn. It is also very flexible and can be used to create any kind of web application.')
        ]})
    }
}

oshare(PageIndex);
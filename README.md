# Ophose

[![GPL License Alternative](https://img.shields.io/badge/licence-GPL_Licence_Alternative-blue)](https://ophose.ah4.fr/licence)
[![Latest Version](https://img.shields.io/github/v/release/ah-4/ophose-release.svg)](https://github.com/ah4/ophose-release/releases)

# [OPHOSE - Visit the the official website](https://ophose.dev/)

Ophose is a simple, lightweight, and flexible framework for building web applications. It handles both front-end and back-end, and you can even download community resources such as as components or environments.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

## Installation

To get started with it, simply clone this repository, then if you want to get started faster, create a free account on [Ophose official website](https://ophose.dev) and get your API Key then, put it in your project.oconf then finally type:

```bash
php ocl ophose install
```

This will install all the dependencies assigned in your `project.oconf` file. You can then start building your application using the provided components and utilities.

## Usage

You can check out the [the full tutorial here](https://ophose.dev/tutorial/getting-started). Here's a quick example to get you started: 

```javascript
class YourComponent extends Ophose.Component {
    constructor(props) {
        super(props);
    }

    handleClick() {
        alert('You clicked the button!');
    }

    render() {
        return _div(
            _h1('Hello, World!'),
            _p('This is a simple example of using Ophose.'),
            _button({ onclick: this.handleClick }, 'Click me')
        ),
    }
}
```

This will create a simple component that renders a heading, a paragraph, and a button. When the button is clicked, it will display an alert with the message "You clicked the button!".

## Documentation

Link to the full back-end documentation of Ophose. Include detailed guides, API reference, and any other relevant documentation resources.

[Link to Documentation](https://ophose.dev/docs)

## Contributing

Please make sure to report any issues or bugs you find in the framework. You can also contribute by submitting a pull request with a fix or a new feature. We are always looking for ways to improve the framework and make it more useful for developers.

## License

This project is licensed under the GPL License Alternative - see the [LICENSE](https://ophose.dev/licence) page for details.

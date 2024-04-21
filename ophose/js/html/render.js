String.prototype.prettyHashCode = function() {
    var hash = 0;
    for (var i = 0; i < this.length; i++) {
        var char = this.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash;
    }
    return 'h' + Math.abs(hash);
}

const defined = '__ophs_defined';
const htmlProperties = ["name", "id"];
const htmlAttrs = ["accept","accept-charset","accesskey","action","align","alt","async","autocomplete","autofocus","autoplay","bgcolor","border","charset","checked","cite","color","cols","colspan","content","contenteditable","controls","coords","data","datetime","defer","dir","disabled","download","draggable","enctype","for","form","headers","height","hidden","high","href","hreflang","http-equiv","id","integrity","ismap","itemprop","keytype","kind","label","lang","list","loop","low","max","maxlength","media","method","min","multiple","muted","name","novalidate","open","optimum","pattern","placeholder","poster","preload","radiogroup","readonly","rel","required","reversed","rows","rowspan","sandbox","scope","scoped","selected","shape","size","sizes","span","spellcheck","src","srcdoc","srclang","srcset","start","step","style","tabindex","target","title","translate","type","usemap","value","width","wrap"];
const htmlEvents = ["onabort", "onautocomplete", "onautocompleteerror", "onblur", "oncancel", "oncanplay", "oncanplaythrough", "onchange", "onclick", "onclose", "oncontextmenu", "oncuechange", "ondblclick", "ondrag", "ondragend", "ondragenter", "ondragexit", "ondragleave", "ondragover", "ondragstart", "ondrop", "ondurationchange", "onemptied", "onended", "onerror", "onfocus", "oninput", "oninvalid", "onkeydown", "onkeypress", "onkeyup", "onload", "onloadeddata", "onloadedmetadata", "onloadstart", "onmousedown", "onmouseenter", "onmouseleave", "onmousemove", "onmouseout", "onmouseover", "onmouseup", "onmousewheel", "onpause", "onplay", "onplaying", "onprogress", "onratechange", "onreset", "onresize", "onscroll", "onseeked", "onseeking", "onselect", "onshow", "onsort", "onstalled", "onsubmit", "onsuspend", "ontimeupdate", "ontoggle", "onvolumechange", "onwaiting"];
const ophAttrs = ["_name"];

class ___render___ {

    static __placedOphoseInstances = [];

    /**
     * Renders an ophose object to a DOM node.
     * @param {*} oph the ophose object to render
     * @returns {Node} the DOM node
     */
    static toNode(oph, shouldBePlaced = false) {

        if(oph === undefined || oph === null || oph === false) return document.createTextNode("");

        if(!___render___.isOphoseObject(oph)) {
            dev.error("RenderException: Invalid ophose object.", oph);
            return undefined;
        }

        // Adding HTML & Event attributes to a node from object
        const giveAttrsAndEventsToNode = (element, node) => {
            
            for (let attribute in element) {
                if (ophAttrs.includes(attribute)) {
                    node.setAttribute(attribute, element[attribute]);
                    continue;
                }
                if(element[attribute] === defined) {
                    node.setAttribute(attribute, '');
                }
                if (element.hasOwnProperty(attribute)) {
                    if (element[attribute] !== undefined && htmlAttrs.includes(attribute)) {
                        node.setAttribute(attribute, element[attribute]);
                        if(htmlProperties.includes(attribute)) node[attribute] = element[attribute];
                        continue;
                    }
                    if (htmlEvents.includes(attribute)) {
                        node[attribute] = element[attribute];
                        continue;
                    }
                    if (element.className || element.class) {
                        if (element.class) element.className = element.class;
                        for (let className of element.className.split(" ")) {
                            if(className == "") continue;
                            node.classList.add(className);
                        }
                    }
                }
            }
        };

        if (Array.isArray(oph)) {
            dev.error("RenderException: Array render without parent is not supported at the moment. Use a parent element or place the array in a children element.");
            return undefined;
        };

        if(oph instanceof Node) {
            return oph;
        }

        // Case: oph is a string
        if(typeof oph == "string" || typeof oph == "number") {
            return document.createTextNode(oph);
        }

        // Case: oph is a component
        if (oph instanceof ___component___) { 
            let rendered = oph.render();
            if(rendered instanceof PlacedLive) {
                rendered.selfClassName = oph.__getComponentUniqueId();
            }
            let node = ___render___.toNode(rendered, shouldBePlaced);
            let nodeToGiveAttrsAndEvents = node;
            if(oph.__propsOn) {
                // loop on children to find the input
                for (let child of node.children) {
                    let name = child.getAttribute('_name');
                    if(!name) continue;
                    if(name == oph.__propsOn) {
                        nodeToGiveAttrsAndEvents = child;
                        break;
                    }
                }
            }
            giveAttrsAndEventsToNode(oph.props, nodeToGiveAttrsAndEvents);
            if (shouldBePlaced) {
                oph.__place(node);
                ___render___.__placedOphoseInstances.push(oph);
                node['o'] = oph;
            }
            return node;
        }

        // Case: oph is a Live
        if (oph instanceof Live) { 
            let textNode = document.createTextNode(oph.get());
            oph.__placedLiveTextNodes.push(textNode);
            return textNode;
        }

        // Case: oph is a placed live (with callback so on)
        if (oph instanceof PlacedLive) {
            let lives = oph.lives;
            let callback = oph.callback;
            let args = lives.map((live) => live.get());
            let newOph = callback(...args);
            let node = ___render___.toNode(newOph, shouldBePlaced);
            oph.node = node;
            if(oph.selfClassName) oph.node.classList.add(oph.selfClassName);
            return node;
        }

        // Case Default: oph is a dict
        if (typeof oph == "object") { 
            let ophNode = document.createElement(oph._);
            if(oph.c) oph.children = oph.c;
            if (oph.children) {
                if(Array.isArray(oph.children)){
                    for (let child of oph.children) {
                        if (Array.isArray(child)) {
                            for (let childChild of child) {
                                let childNode = ___render___.toNode(childChild, shouldBePlaced);
                                if(childNode) {
                                    ophNode.appendChild(childNode);
                                }
                            }
                            continue;
                        }
                        let childNode = ___render___.toNode(child, shouldBePlaced);
                        if(childNode) {
                            ophNode.appendChild(childNode);
                        }
                    }
                } else {
                    let childNode = ___render___.toNode(oph.children, shouldBePlaced);
                    if(childNode) {
                        ophNode.appendChild(childNode);
                    }
                }
            }
            if (oph.text) {
                ophNode.textContent = oph.text;
            }

            giveAttrsAndEventsToNode(oph, ophNode);
            
            if (oph.html || oph.innerHTML) {
                ophNode.innerHTML = oph.html || oph.innerHTML;
            }
            return ophNode;
        }
        return undefined;
    }

    static isOphoseObject(object) {
        return typeof object == "string" ||
            typeof object == "number" ||
            object instanceof ___component___ ||
            object instanceof Live ||
            object instanceof PlacedLive ||
            (typeof object == "object" && object._) ||
            Array.isArray(object) ||
            object instanceof Node;
    }
}

/**
 * Shortcut function to create an ophose object.
 * @param {string} tag the HTML tag
 * @param {object|string|number|Array|___component___|Live|PlacedLive|Node} propsOrChildren the props if object without '_' key, else children
 */
function _(tag, ...propsOrChildren) {
    let object = {_: tag};
    let children = [];

    if(propsOrChildren[0] && !___render___.isOphoseObject(propsOrChildren[0])) {
        object = {...object, ...propsOrChildren.shift()};
    }
    
    for(let arg of propsOrChildren) {
        if(Array.isArray(arg)) {
            children.push(...arg);
            continue;
        }
        children.push(arg);
    }

    if(children.length > 0) object.children = children;
    return object;
}
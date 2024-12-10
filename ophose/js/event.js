/**
 * Class representing ophose event
 */
class ___event___ {

    // Associative array of eventname: callback
    static __eventCallbacks = {};

    /**
     * Registers a callback for an event
     * (note that callbacks are registered in apparition order)
     * @param {string} eventName the event name ("onPageLoad" for example)
     * @param {object} callback the callback (called with: value passed
     * by event caller, event name)
     */
    static addListener(eventName, callback) {
        eventName = eventName.trim().toLowerCase();
        if (!___event___.__eventCallbacks[eventName]) {
            ___event___.__eventCallbacks[eventName] = [];
        }
        ___event___.__eventCallbacks[eventName].push(callback);
    }


    static callEvent(eventName, value) {
        eventName = eventName.trim().toLowerCase();
        if (!___event___.__eventCallbacks[eventName]) {
            return;
        }
        for (const callback of ___event___.__eventCallbacks[eventName]) {
            callback(value, eventName);
        }
    }

}

// History listener
window.addEventListener("popstate", (event) => {
    if (event.state === null) return;
    ___app___.__go(event.state);
});
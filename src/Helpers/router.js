"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.setCurrentRouteName = exports.getCurrentRouteName = void 0;
var currentRouteName = false;
function getCurrentRouteName() {
    return currentRouteName;
}
exports.getCurrentRouteName = getCurrentRouteName;
function setCurrentRouteName(name) {
    if (currentRouteName === false)
        currentRouteName = name;
    else
        console.error('An error occurred while setting the name of the current route. The route name has already been set!');
}
exports.setCurrentRouteName = setCurrentRouteName;

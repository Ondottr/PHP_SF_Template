"use strict";
/*
 * Copyright © 2018-2022, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
var axios_1 = require("axios");
var beforeRequest = function () {
    // TODO:: add something request
};
var getHeaders = function () {
    beforeRequest();
    return {
        headers: {
            'Content-Type': 'application/merge-patch+json'
        },
    };
};
function parseResponse(response) {
    return response;
}
function parseErrorResponse(error) {
    if (error.response) {
        if (error.response.status === 401) {
            window.location.replace('/auth/login');
            console.log('Unauthorized!');
        }
        else {
            // The request was made ...
            var errorMsg = 'Error: The server responded with a status code that falls out of the range of 2xx';
            if (process.env.APP_ENV === process.env.DEV_ENV) {
                console.log('=============================>>', errorMsg);
                console.log('Response data:', error.response.data, "Response status: ".concat(error.response.status), 'Response headers:', error.response.headers, 'Request config:', error.config);
            }
            else
                console.log(errorMsg);
        }
    }
    else if (error.request) {
        // The request was made but ...
        var errorMsg = 'Error: No response was received';
        if (process.env.APP_ENV === process.env.DEV_ENV) {
            console.log('=============================>>', errorMsg);
            console.log('Request:', 
            // `error.request` is an instance of XMLHttpRequest in the browser
            // and an instance of http.ClientRequest in node.js
            error.request, 'Request config:', error.config);
        }
        else
            console.log(errorMsg);
    }
    else {
        var errorMsg = 'Something happened in setting up the request that triggered an Error';
        if (process.env.APP_ENV === process.env.DEV_ENV) {
            console.log('=============================>>', "".concat(errorMsg));
            console.log(error.message, 'Request config:', error.config);
        }
        else
            console.log(errorMsg);
    }
    console.log('<<=============================');
    return error.response.data;
}
var HttpProvider = /** @class */ (function () {
    function HttpProvider() {
    }
    HttpProvider.prototype.get = function (url) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, axios_1.default
                            .get(url, getHeaders())
                            .then(function (response) {
                            return parseResponse(response);
                        })
                            .catch(function (error) {
                            return parseErrorResponse(parseResponse(error));
                        })];
                    case 1: return [2 /*return*/, _a.sent()];
                }
            });
        });
    };
    HttpProvider.prototype.post = function (url, data) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, axios_1.default
                            .post(url, data, getHeaders())
                            .then(function (response) {
                            return parseResponse(response);
                        })
                            .catch(function (error) {
                            return parseErrorResponse(parseResponse(error));
                        })];
                    case 1: return [2 /*return*/, _a.sent()];
                }
            });
        });
    };
    HttpProvider.prototype.put = function (url, data) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, axios_1.default
                            .put(url, data, getHeaders())
                            .then(function (response) {
                            return parseResponse(response);
                        })
                            .catch(function (error) {
                            return parseErrorResponse(parseResponse(error));
                        })];
                    case 1: return [2 /*return*/, _a.sent()];
                }
            });
        });
    };
    HttpProvider.prototype.patch = function (url, data) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, axios_1.default
                            .patch(url, data, getHeaders())
                            .then(function (response) {
                            return parseResponse(response);
                        })
                            .catch(function (error) {
                            return parseErrorResponse(parseResponse(error));
                        })];
                    case 1: return [2 /*return*/, _a.sent()];
                }
            });
        });
    };
    HttpProvider.prototype.delete = function (url) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0: return [4 /*yield*/, axios_1.default
                            .delete(url, getHeaders())
                            .then(function (response) {
                            return parseResponse(response);
                        })
                            .catch(function (error) {
                            return parseErrorResponse(parseResponse(error));
                        })];
                    case 1: return [2 /*return*/, _a.sent()];
                }
            });
        });
    };
    return HttpProvider;
}());
var Http = new HttpProvider();
exports.default = Http;

/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
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

import axios, {AxiosResponse} from 'axios';


const beforeRequest = (): void => {
    // TODO:: add something request
};

const getHeaders = (): object => {
    beforeRequest();

    return {
        headers: {
            'Content-Type': 'application/merge-patch+json'
        },
    };
};

function parseResponse(response: AxiosResponse): AxiosResponse {
    return response;
}

function parseErrorResponse(error) {
    if (error.response) {
        if (error.response.status === 401) {
            window.location.replace('/auth/login');

            console.log('Unauthorized!');
        } else {
            // The request was made ...
            const errorMsg =
                'Error: The server responded with a status code that falls out of the range of 2xx';

            if (process.env.APP_ENV === 'dev') {
                console.log('=============================>>', errorMsg);

                console.log(
                    'Response data:',
                    error.response.data,
                    `Response status: ${error.response.status}`,
                    'Response headers:',
                    error.response.headers,
                    'Request config:',
                    error.config,
                );
            } else console.log(errorMsg);
        }
    } else if (error.request) {
        // The request was made but ...
        const errorMsg = 'Error: No response was received';

        if (process.env.APP_ENV === 'dev') {
            console.log('=============================>>', errorMsg);
            console.log(
                'Request:',
                // `error.request` is an instance of XMLHttpRequest in the browser
                // and an instance of http.ClientRequest in node.js
                error.request,
                'Request config:',
                error.config,
            );
        } else console.log(errorMsg);
    } else {
        const errorMsg =
            'Something happened in setting up the request that triggered an Error';

        if (process.env.APP_ENV === 'dev') {
            console.log('=============================>>', `${errorMsg}`);
            console.log(error.message, 'Request config:', error.config);
        } else console.log(errorMsg);
    }

    console.log('<<=============================');

    return error.response.data;
}

class HttpProvider {
    async get(url: string): Promise<AxiosResponse> {
        return await axios
            .get(url, getHeaders())
            .then((response) => {
                return parseResponse(response);
            })
            .catch((error) => {
                return parseErrorResponse(parseResponse(error));
            });
    }

    async post(url: string, data: object): Promise<AxiosResponse> {
        return await axios
            .post(url, data, getHeaders())
            .then((response) => {
                return parseResponse(response);
            })
            .catch((error: AxiosResponse) => {
                return parseErrorResponse(parseResponse(error));
            });
    }

    async put(url: string, data: object): Promise<AxiosResponse> {
        return await axios
            .put(url, data, getHeaders())
            .then((response) => {
                return parseResponse(response);
            })
            .catch((error) => {
                return parseErrorResponse(parseResponse(error));
            });
    }

    async patch(url: string, data: object): Promise<AxiosResponse> {
        return await axios
            .patch(url, data, getHeaders())
            .then((response) => {
                return parseResponse(response);
            })
            .catch((error) => {
                return parseErrorResponse(parseResponse(error));
            });
    }

    async delete(url: string): Promise<AxiosResponse> {
        return await axios
            .delete(url, getHeaders())
            .then((response) => {
                return parseResponse(response);
            })
            .catch((error) => {
                return parseErrorResponse(parseResponse(error));
            });
    }
}

const Http = new HttpProvider();

export default Http;

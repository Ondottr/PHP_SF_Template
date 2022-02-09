/*
 * Copyright © 2018-2022, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

import Http from '../Providers/HttpProvider';

interface userObj {
    id: bigint;
    ep: number;
    login: string;
    email: string;
    userGroup: number;
}

export default class User implements userObj {
    private readonly _id: bigint;
    private readonly _ep: number;
    private readonly _login: string;
    private readonly _email: string;
    private readonly _userGroup: number;
    private _chat_messages: number;

    /**
     * @param {Array<string|number>} userObj
     */
    constructor(userObj: userObj) {
        this._id = userObj.id;
        this._ep = userObj.ep;
        this._login = userObj.login;
        this._email = userObj.email;
        this._userGroup = userObj.userGroup;
    }

    private static _instance?: User = null;

    static get instance(): User {
        return this._instance;
    }

    static set instance(value: User) {
        if (this._instance !== null) {
            console.error('User class already initialized!');

            return;
        }

        this._instance = value;
        if (process.env.APP_ENV === process.env.DEV_ENV || process.env.APP_ENV === process.env.TEST_ENV)
            console.log('User class initialized! here is User class:', User.instance);
    }

    get chatMessages(): number {
        return this._chat_messages;
    }

    set chatMessages(value: number) {
        this._chat_messages = value;
    }

    get id(): bigint {
        return this._id;
    }

    get ep(): number {
        return this._ep;
    }

    public get login(): string {
        return this._login;
    }

    get email(): string {
        return this._email;
    }

    get userGroup(): number {
        return this._userGroup;
    }

}


export async function fetchUser(): Promise<User> {
    if (User.instance !== null) {
        console.error('User class already initialized!');

        return;
    }

    const response = await Http.get('/api/auth/me');

    if (response && response.data)
        return (User.instance = new User(response.data));

}

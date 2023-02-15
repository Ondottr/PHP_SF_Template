import Http from '../Providers/HttpProvider';

interface userObj {
    id: bigint;
    login: string;
    email: string;
    userGroup: number;
}

export default class User implements userObj {
    private readonly _id: bigint;
    private readonly _login: string;
    private readonly _email: string;
    private readonly _userGroup: number;
    private _chat_messages: number;

    /**
     * @param {Array<string|number>} userObj
     */
    constructor(userObj: userObj) {
        this._id = userObj.id;
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
        if (
            process.env.APP_ENV === 'dev' || process.env.APP_ENV === 'test'
        )
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

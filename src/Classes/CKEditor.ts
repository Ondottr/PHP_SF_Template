interface CKEditorObj {
    converter;
    watchdog;
    editor;
}

export default class CKEditor implements CKEditorObj {
    private readonly _converter: bigint;
    private readonly _watchdog: number;
    private readonly _editor: string;

    constructor(converter, watchdog, editor) {
        this._converter = converter;
        this._watchdog = watchdog;
        this._editor = editor;
    }

    private static _instance?: CKEditor = null;

    static get instance(): CKEditor {
        return this._instance;
    }

    static set instance(value: CKEditor) {
        if (this._instance !== null) {
            console.error('CKEditor class already initialized!');

            return;
        }

        this._instance = value;
        if (process.env.APP_ENV === 'dev' || process.env.APP_ENV === 'test')
            console.log(
                'CKEditor class initialized! here is CKEditor class:',
                CKEditor.instance,
            );
    }

    get converter() {
        return this._converter;
    }

    get watchdog() {
        return this._watchdog;
    }

    get editor() {
        return this._editor;
    }
}

export async function initializeCKEditor(
    converter,
    watchdog,
    editor,
): Promise<CKEditor> {
    if (CKEditor.instance !== null) {
        console.error('CKEditor class already initialized!');

        return;
    }

    return (CKEditor.instance = new CKEditor(converter, watchdog, editor));
}

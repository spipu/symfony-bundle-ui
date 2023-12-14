// spipu-translator.js

class SpipuTranslator {
    constructor() {
        this.translations = []
    }

    initTranslations(translations) {
        this.translations = translations;
    }

    addTranslations(translations) {
        for (let key in translations) {
            this.addTranslation(key, translations[key]);
        }
    }

    addTranslation(key, value) {
        this.translations[key] = value;
    }

    trans(value, parameters) {
        if (typeof parameters === 'undefined') {
            parameters = [];
        }

        if (value in this.translations) {
            value = this.translations[value];
        }

        for (let i = 0; i < parameters.length; i++) {
            value = value.replace('%' + (i+1), parameters[i]);
        }

        return value;
    };
}

window.translator = new SpipuTranslator();

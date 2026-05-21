import js from "@eslint/js";
import globals from "globals";

export default [
    {
        ignores: ["assets/vendor/**/*"]
    },
    js.configs.recommended,
    {
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                ...globals.browser,
                ...globals.jquery,
                $: "readonly",
                jQuery: "readonly"
            }
        },
        rules: {
            "no-unused-vars": "warn",
            "no-console": "off"
        }
    }
];
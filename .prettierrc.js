module.exports = {
    printWidth: 100,
    trailingComma: "all",
    singleQuote: true,
    semi: true,
    bracketSameLine: false,
    bracketSpacing: true,
    tabWidth: 2,
    vueIndentScriptAndStyle: true,
    overrides: [
        {
            files: "*.php",
            options: {
                phpVersion: "8.1",
                tabWidth: 4,
                printWidth: 120,
                trailingCommaPHP: true,
            },
        },
        {
            files: "*.yml",
            options: {
                singleQuote: false,
            },
        },
    ],
};

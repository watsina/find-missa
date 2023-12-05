module.exports = {
    env: {
        node: true,
    },
    extends: [
        'eslint:recommended',
        'plugin:vue/vue3-essential',
        'plugin:vue/vue3-strongly-recommended',
        'prettier',
    ],
    plugins: ['vue', 'unused-imports'],
    rules: {
        // override/add rules settings here, such as:
        // 'vue/no-unused-vars': 'error'
        'unused-imports/no-unused-imports': 'warn',
        'vue/max-attributes-per-line': 'off',
        'vue/multi-word-component-names': 'off',
        'no-undef': 'warn',
        'vue/attributes-order': [
            'warn',
            {
                alphabetical: true,
            },
        ],
    },
};

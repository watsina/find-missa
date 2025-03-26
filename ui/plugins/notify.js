import { reactive } from 'vue';

export default reactive({
  color: 'info',
  text: '',
  props: {},
  confirmation: {},
  timeoutId: null,

  close() {
    this.text = null;
    clearTimeout(this.timeoutId);
  },
  show(color, text, props = {}) {
    this.color = color;
    this.text = text;
    this.props = props || {};

    if (props.autoClose === false) {
      return;
    }

    this.timeoutId = setTimeout(() => {
      this.text = null;
    }, 90000);
  },
  success(...args) {
    this.show('success', ...args);
  },
  errorFromResponse(error, defaultError = 'Something went wrong') {
    this.show('error', error?.response?.data?.message || error?.message || defaultError);
  },
  error(...args) {
    this.show('error', ...args);
  },
  warning(...args) {
    this.show('warning', ...args);
  },
  info(...args) {
    this.show('info', ...args);
  },

  handleConfirm(value) {
    if (value) {
      return this.confirmation?.resolve(true);
    }

    return this.confirmation?.reject(false);
  },
  confirm(message, options = {}) {
    this.confirmation = { message, options };

    return new Promise((resolve, reject) => {
      this.confirmation.resolve = resolve;
      this.confirmation.reject = reject;
    }).finally(() => {
      this.confirmation = {};
    });
  },
});

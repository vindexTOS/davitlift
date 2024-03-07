export default {
    install(Vue) {
        Vue.prototype.$hasAccess = function (minLvl) {
            console.log(minLvl)
            // Access store state or commit/mutate store
            console.log('Accessing store from global function:', this.$store.state);

            // Your logic here
            console.log('This is a global function');
        }
    }
}

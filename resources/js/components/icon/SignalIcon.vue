<script>
export default {
    name: "SignalIcon",
    props: {
        signal : {
            type: Number,
            default : 1,
        }
    }
}
</script>

<template>
    <template v-if="signal === 0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3zm4-3h2v8H7zM21 3h-2v14.59l-2-2V7h-2v6.59l-2-2V10h-1.59l-7.7-7.71l-1.42 1.42l18 18l1.42-1.42l-.71-.7V3zm-6 18h1.88L15 19.12V21zm-4 0h2v-3.88l-2-2V21z"/></svg>
    </template>
    <template v-if="signal === 1">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3z"/></svg>
    </template>
    <template v-if="signal === 2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3zm4-3h2v8H7z"/></svg>    </template>
    <template v-if="signal === 3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3zm4-3h2v8H7zm4-3h2v11h-2z"/></svg>
    </template>
    <template v-if="signal === 4">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3zm4-3h2v8H7zm4-3h2v11h-2zm4-3h2v14h-2z"/></svg>
    </template>
    <template v-if="signal === 5">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 16h2v5H3zm4-3h2v8H7zm4-3h2v11h-2zm4-3h2v14h-2zm4-3h2v17h-2z"/></svg>
    </template>
</template>

<style scoped>

</style>

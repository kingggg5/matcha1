import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
    const message = ref('')
    const type = ref('info') // 'success', 'error', 'warning', 'info'
    const visible = ref(false)
    let timeout = null

    function show(msg, toastType = 'info', duration = 3000) {
        message.value = msg
        type.value = toastType
        visible.value = true

        if (timeout) clearTimeout(timeout)
        timeout = setTimeout(() => {
            visible.value = false
        }, duration)
    }

    function success(msg) {
        show(msg, 'success')
    }

    function error(msg) {
        show(msg, 'error')
    }

    function warning(msg) {
        show(msg, 'warning')
    }

    function info(msg) {
        show(msg, 'info')
    }

    function hide() {
        visible.value = false
    }

    return {
        message,
        type,
        visible,
        show,
        success,
        error,
        warning,
        info,
        hide
    }
})

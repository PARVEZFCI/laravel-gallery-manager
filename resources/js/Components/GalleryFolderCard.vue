<script setup>
defineProps({
  folder: {
    type: Object,
    required: true
  }
})

defineEmits(['click'])
</script>

<template>
  <div
    @click="$emit('click')"
    class="bg-white rounded-lg shadow hover:shadow-lg transition cursor-pointer overflow-hidden group"
  >
    <div class="p-6">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ folder.formatted_date }}</h3>
            <p class="text-sm text-gray-500">{{ folder.folder_date }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-gray-500">Images</p>
          <p class="font-semibold text-gray-900">{{ folder.image_count }}</p>
        </div>
        <div>
          <p class="text-gray-500">Size</p>
          <p class="font-semibold text-gray-900">{{ folder.formatted_size }}</p>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t border-gray-100">
        <p class="text-xs text-gray-400">
          Last updated {{ new Date(folder.updated_at).toLocaleDateString() }}
        </p>
      </div>
    </div>

    <div class="bg-gray-50 px-6 py-3 group-hover:bg-blue-50 transition">
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-600 group-hover:text-blue-600">View images</span>
        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </div>
    </div>
  </div>
</template>

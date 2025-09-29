<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="mediaPicker({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            multiple: @js($getMultiple()),
            allowUpload: @js($getAllowUpload()),
            maxFiles: @js($getMaxFiles()),
            allowedMimes: @js($getAllowedMimes()),
            fieldKey: @js($getFieldKey()),
            group: @js($getGroup()),
            reorderable: @js($getReorderable()),
            mediaItems: @js($getMediaItems()),
        })"
        class="space-y-4"
    >
        <!-- Selected Media Display -->
        <div x-show="selectedMedia.length > 0" class="space-y-2">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">
                    Selected Media
                    <span x-text="`(${selectedMedia.length}${multiple ? `/${maxFiles}` : ''})`"></span>
                </h4>
                <button
                    type="button"
                    @click="clearSelection()"
                    class="text-sm text-red-600 hover:text-red-800"
                >
                    Clear All
                </button>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <template x-for="(item, index) in selectedMedia" :key="item.id">
                    <div class="relative group">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img
                                x-show="item.is_image"
                                :src="item.thumbnail_url"
                                :alt="item.alt || item.original_name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                x-show="!item.is_image"
                                class="w-full h-full flex items-center justify-center bg-gray-200"
                            >
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Remove Button -->
                        <button
                            type="button"
                            @click="removeMedia(index)"
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            ×
                        </button>
                        
                        <!-- Reorder Handles -->
                        <div
                            x-show="reorderable && multiple && selectedMedia.length > 1"
                            class="absolute bottom-1 left-1 right-1 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <button
                                type="button"
                                @click="moveUp(index)"
                                :disabled="index === 0"
                                class="w-6 h-6 bg-black bg-opacity-50 text-white rounded text-xs flex items-center justify-center disabled:opacity-50"
                            >
                                ↑
                            </button>
                            <button
                                type="button"
                                @click="moveDown(index)"
                                :disabled="index === selectedMedia.length - 1"
                                class="w-6 h-6 bg-black bg-opacity-50 text-white rounded text-xs flex items-center justify-center disabled:opacity-50"
                            >
                                ↓
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button
                type="button"
                @click="openModal()"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Browse Media Library
            </button>
            
            <button
                x-show="allowUpload"
                type="button"
                @click="openUploadModal()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Upload New
            </button>
        </div>

        <!-- Media Library Modal -->
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Select Media</h3>
                            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Media Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 max-h-96 overflow-y-auto">
                            <template x-for="item in availableMedia" :key="item.id">
                                <div
                                    @click="toggleSelection(item)"
                                    :class="[
                                        'relative cursor-pointer rounded-lg overflow-hidden border-2 transition-colors',
                                        isSelected(item) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="aspect-square bg-gray-100">
                                        <img
                                            x-show="item.is_image"
                                            :src="item.thumbnail_url"
                                            :alt="item.alt || item.original_name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            x-show="!item.is_image"
                                            class="w-full h-full flex items-center justify-center bg-gray-200"
                                        >
                                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Selection Indicator -->
                                    <div
                                        x-show="isSelected(item)"
                                        class="absolute top-2 right-2 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs"
                                    >
                                        ✓
                                    </div>
                                    
                                    <div class="p-2">
                                        <p class="text-xs text-gray-600 truncate" x-text="item.original_name"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Modal Actions -->
                        <div class="mt-6 flex justify-end gap-2">
                            <button
                                type="button"
                                @click="closeModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="confirmSelection()"
                                class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                            >
                                Select
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

<script>
function mediaPicker(config) {
    return {
        state: config.state,
        multiple: config.multiple,
        allowUpload: config.allowUpload,
        maxFiles: config.maxFiles,
        allowedMimes: config.allowedMimes,
        fieldKey: config.fieldKey,
        group: config.group,
        reorderable: config.reorderable,
        mediaItems: config.mediaItems,
        
        showModal: false,
        availableMedia: [],
        selectedMedia: [],
        
        init() {
            this.selectedMedia = this.mediaItems || [];
            this.loadAvailableMedia();
        },
        
        loadAvailableMedia() {
            // This would typically make an AJAX call to load media items
            // For now, we'll use a placeholder
            this.availableMedia = [];
        },
        
        openModal() {
            this.showModal = true;
            this.loadAvailableMedia();
        },
        
        closeModal() {
            this.showModal = false;
        },
        
        openUploadModal() {
            // This would open a file upload modal
            console.log('Upload modal would open here');
        },
        
        toggleSelection(item) {
            if (this.isSelected(item)) {
                this.selectedMedia = this.selectedMedia.filter(media => media.id !== item.id);
            } else {
                if (!this.multiple) {
                    this.selectedMedia = [item];
                } else if (this.selectedMedia.length < this.maxFiles) {
                    this.selectedMedia.push(item);
                }
            }
        },
        
        isSelected(item) {
            return this.selectedMedia.some(media => media.id === item.id);
        },
        
        confirmSelection() {
            this.state = this.multiple 
                ? this.selectedMedia.map(item => item.id)
                : this.selectedMedia[0]?.id || null;
            this.closeModal();
        },
        
        removeMedia(index) {
            this.selectedMedia.splice(index, 1);
            this.state = this.multiple 
                ? this.selectedMedia.map(item => item.id)
                : this.selectedMedia[0]?.id || null;
        },
        
        clearSelection() {
            this.selectedMedia = [];
            this.state = this.multiple ? [] : null;
        },
        
        moveUp(index) {
            if (index > 0) {
                const item = this.selectedMedia.splice(index, 1)[0];
                this.selectedMedia.splice(index - 1, 0, item);
                this.state = this.selectedMedia.map(item => item.id);
            }
        },
        
        moveDown(index) {
            if (index < this.selectedMedia.length - 1) {
                const item = this.selectedMedia.splice(index, 1)[0];
                this.selectedMedia.splice(index + 1, 0, item);
                this.state = this.selectedMedia.map(item => item.id);
            }
        }
    }
}
</script>

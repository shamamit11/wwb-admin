import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const Delta = Quill.import('delta');
const BaseImageBlot = Quill.import('formats/image');

const toolbar = [
    [{ header: [1, 2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ script: 'sub' }, { script: 'super' }],
    [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
    [{ align: [] }],
    [{ color: [] }, { background: [] }],
    ['blockquote', 'code-block'],
    ['link', 'image', 'video'],
    ['clean'],
];

function normalizeImageValue(value, node = null) {
    if (typeof value === 'string') {
        return {
            src: value,
            alt: node?.getAttribute('alt') ?? '',
            mediaId: node?.getAttribute('data-media-id') ?? '',
        };
    }

    if (value && typeof value === 'object') {
        return {
            src: value.src ?? value.url ?? '',
            alt: value.alt ?? '',
            mediaId: value.mediaId ?? value['data-media-id'] ?? '',
        };
    }

    return {
        src: node?.getAttribute('src') ?? '',
        alt: node?.getAttribute('alt') ?? '',
        mediaId: node?.getAttribute('data-media-id') ?? '',
    };
}

class MediaImageBlot extends BaseImageBlot {
    static create(value) {
        const node = super.create();
        const image = normalizeImageValue(value);

        node.setAttribute('src', image.src);

        if (image.alt) {
            node.setAttribute('alt', image.alt);
        } else {
            node.removeAttribute('alt');
        }

        if (image.mediaId) {
            node.setAttribute('data-media-id', String(image.mediaId));
        } else {
            node.removeAttribute('data-media-id');
        }

        return node;
    }

    static formats(node) {
        return {
            alt: node.getAttribute('alt') ?? '',
            mediaId: node.getAttribute('data-media-id') ?? '',
        };
    }

    static value(node) {
        return normalizeImageValue(null, node);
    }

    format(name, value) {
        if (name === 'alt') {
            if (value) {
                this.domNode.setAttribute('alt', value);
            } else {
                this.domNode.removeAttribute('alt');
            }

            return;
        }

        if (name === 'mediaId' || name === 'data-media-id') {
            if (value) {
                this.domNode.setAttribute('data-media-id', value);
            } else {
                this.domNode.removeAttribute('data-media-id');
            }

            return;
        }

        super.format(name, value);
    }
}

MediaImageBlot.blotName = 'image';
MediaImageBlot.tagName = 'IMG';

Quill.register(MediaImageBlot, true);

function normalizeHtml(html) {
    return html === '<p><br></p>' ? '' : html;
}

function updateHiddenField(field, value) {
    if (!field) {
        return;
    }

    field.value = value;
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
}

function notifyImageEmbedPolicy() {
    window.alert('Inline file images are disabled in the editor. Use the Quill image picker to choose existing media or upload through the backend media API first.');
}

function insertMediaImage(quill, media) {
    const image = normalizeEditorMedia(media);
    const range = quill.getSelection(true);
    const index = range ? range.index : quill.getLength();

    quill.insertEmbed(index, 'image', {
        src: image.url,
        alt: image.alt_text ?? image.original_filename ?? '',
        mediaId: image.id,
    }, 'user');
    quill.setSelection(index + 1, 0, 'silent');
}

function preventInlineImageFiles(quill) {
    const blockImageFileInsert = (event) => {
        const files = Array.from(event.dataTransfer?.files ?? event.clipboardData?.files ?? []);
        const hasImageFile = files.some((file) => file.type.startsWith('image/'));

        if (!hasImageFile) {
            return;
        }

        event.preventDefault();
        notifyImageEmbedPolicy();
    };

    quill.root.addEventListener('drop', blockImageFileInsert);
    quill.root.addEventListener('paste', blockImageFileInsert);
}

function readJsonSource(selector, fallback = null) {
    if (!selector) {
        return fallback;
    }

    const source = document.querySelector(selector);

    if (!source) {
        return fallback;
    }

    try {
        return JSON.parse(source.textContent ?? 'null') ?? fallback;
    } catch {
        return fallback;
    }
}

function readInitialHtml(element) {
    const initialHtml = readJsonSource(element.dataset.quillInitialHtmlSource, null);

    if (typeof initialHtml === 'string') {
        return initialHtml;
    }

    return element.dataset.quillInitialHtml ?? '';
}

function normalizeEditorMedia(item) {
    return {
        id: item.id,
        url: item.url ?? '',
        alt_text: item.alt_text ?? '',
        caption: item.caption ?? '',
        original_filename: item.original_filename ?? item.name ?? 'Media asset',
        is_image: item.is_image ?? true,
        mime_type: item.mime_type ?? '',
    };
}

function readMediaLibrary(element) {
    const library = readJsonSource(element.dataset.quillMediaLibrarySource, []);

    if (!Array.isArray(library)) {
        return [];
    }

    return library
        .map((item) => normalizeEditorMedia(item))
        .filter((item) => item.url && item.is_image);
}

function writeMediaLibrary(element, items) {
    const selector = element.dataset.quillMediaLibrarySource;

    if (!selector) {
        return;
    }

    const source = document.querySelector(selector);

    if (!source) {
        return;
    }

    source.textContent = JSON.stringify(items);
}

function upsertMediaLibraryItem(element, media) {
    const normalized = normalizeEditorMedia(media);
    const items = readMediaLibrary(element).filter((item) => item.id !== normalized.id);
    items.unshift(normalized);
    writeMediaLibrary(element, items);
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function uploadInlineMedia(element, file, fields = {}) {
    const uploadUrl = element.dataset.quillUploadUrl;

    if (!uploadUrl) {
        throw new Error('Inline media upload is not configured for this editor.');
    }

    const formData = new FormData();
    formData.append('file', file);

    if (fields.alt_text) {
        formData.append('alt_text', fields.alt_text);
    }

    if (fields.caption) {
        formData.append('caption', fields.caption);
    }

    const response = await window.fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
        credentials: 'same-origin',
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = payload.message
            ?? Object.values(payload.errors ?? {}).flat()[0]
            ?? 'Inline media upload failed.';

        throw new Error(message);
    }

    return normalizeEditorMedia(payload.data ?? {});
}

function mediaPickerModal() {
    if (window.__widewebblogInlineMediaModal) {
        return window.__widewebblogInlineMediaModal;
    }

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 hidden';
    modal.innerHTML = `
        <div data-inline-media-overlay class="absolute inset-0 bg-[rgba(20,27,43,0.55)]"></div>
        <div class="absolute inset-x-4 top-1/2 mx-auto flex max-h-[85vh] w-full max-w-5xl -translate-y-1/2 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--color-line)] bg-[var(--color-panel)] shadow-[var(--shadow-card)]">
            <div class="flex items-start justify-between gap-4 border-b border-[var(--color-line)] px-6 py-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-muted)]">Inline Media</p>
                    <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em] text-[var(--color-ink)]">Insert Article Image</h2>
                    <p class="mt-2 text-sm text-[var(--color-muted)]">Choose an existing image or upload a new one through the backend media API.</p>
                </div>
                <button type="button" data-inline-media-close class="rounded-[var(--radius-button)] border border-[var(--color-line)] px-3 py-2 text-sm text-[var(--color-muted)] transition-colors hover:bg-[var(--color-panel-soft)] hover:text-[var(--color-ink)]">Close</button>
            </div>

            <div class="border-b border-[var(--color-line)] px-6 py-4">
                <div class="inline-flex rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel-soft)] p-1">
                    <button type="button" data-inline-media-tab="library" class="rounded-[calc(var(--radius-button)-1px)] bg-[var(--color-panel)] px-3 py-2 text-sm font-medium text-[var(--color-ink)] shadow-sm">Media Library</button>
                    <button type="button" data-inline-media-tab="upload" class="rounded-[calc(var(--radius-button)-1px)] px-3 py-2 text-sm font-medium text-[var(--color-muted)]">Upload New</button>
                </div>
                <p data-inline-media-error class="mt-3 hidden rounded-[var(--radius-button)] border border-[color-mix(in_srgb,var(--color-danger)_24%,white)] bg-[color-mix(in_srgb,var(--color-danger)_10%,white)] px-4 py-3 text-sm text-[var(--color-danger-strong)]"></p>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <section data-inline-media-panel="library" class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-[var(--color-muted)]">Select an image that already exists in the media library.</p>
                        <span data-inline-media-count class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-muted)]"></span>
                    </div>
                    <div data-inline-media-library class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
                </section>

                <section data-inline-media-panel="upload" class="hidden space-y-5">
                    <p class="text-sm text-[var(--color-muted)]">Upload a new inline article image. The editor will insert the returned backend media URL and preserve its media ID.</p>
                    <label class="block space-y-2">
                        <span class="text-sm font-medium text-[var(--color-ink)]">Image File</span>
                        <input data-inline-media-file type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg" class="block w-full rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)] file:mr-3 file:rounded-[var(--radius-button)] file:border-0 file:bg-[var(--color-panel-soft)] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[var(--color-ink)]">
                    </label>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-[var(--color-ink)]">Alt Text</span>
                            <input data-inline-media-alt type="text" class="block w-full rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)]" placeholder="Describe the image for accessibility">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm font-medium text-[var(--color-ink)]">Caption</span>
                            <input data-inline-media-caption type="text" class="block w-full rounded-[var(--radius-button)] border border-[var(--color-line)] bg-[var(--color-panel)] px-3.5 py-3 text-sm text-[var(--color-ink)]" placeholder="Optional editorial caption">
                        </label>
                    </div>
                    <div class="flex items-center justify-end gap-3 border-t border-[var(--color-line)] pt-5">
                        <button type="button" data-inline-media-upload class="rounded-[var(--radius-button)] bg-[var(--color-accent)] px-4 py-2.5 text-sm font-medium text-[var(--color-accent-contrast)] transition-colors hover:bg-[var(--color-accent-strong)]">Upload And Insert</button>
                    </div>
                </section>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    window.__widewebblogInlineMediaModal = modal;

    return modal;
}

function setInlineMediaError(modal, message = '') {
    const node = modal.querySelector('[data-inline-media-error]');

    if (!node) {
        return;
    }

    node.textContent = message;
    node.classList.toggle('hidden', message === '');
}

function setInlineMediaTab(modal, activeTab) {
    modal.querySelectorAll('[data-inline-media-tab]').forEach((button) => {
        const isActive = button.getAttribute('data-inline-media-tab') === activeTab;

        button.classList.toggle('bg-[var(--color-panel)]', isActive);
        button.classList.toggle('text-[var(--color-ink)]', isActive);
        button.classList.toggle('shadow-sm', isActive);
        button.classList.toggle('text-[var(--color-muted)]', !isActive);
    });

    modal.querySelectorAll('[data-inline-media-panel]').forEach((panel) => {
        panel.classList.toggle('hidden', panel.getAttribute('data-inline-media-panel') !== activeTab);
    });
}

function renderInlineMediaLibrary(modal, items) {
    const container = modal.querySelector('[data-inline-media-library]');
    const count = modal.querySelector('[data-inline-media-count]');

    if (!container || !count) {
        return;
    }

    count.textContent = `${items.length} image${items.length === 1 ? '' : 's'}`;

    if (items.length === 0) {
        container.innerHTML = '<div class="col-span-full rounded-[var(--radius-card)] border border-dashed border-[var(--color-line)] bg-[var(--color-panel-soft)] px-6 py-10 text-center text-sm text-[var(--color-muted)]">No reusable inline images are loaded for this post yet. Upload one in the next tab.</div>';

        return;
    }

    container.innerHTML = items.map((item) => `
        <button
            type="button"
            data-select-inline-media-id="${item.id}"
            class="overflow-hidden rounded-[var(--radius-card)] border border-[var(--color-line)] bg-[var(--color-panel)] text-left shadow-[var(--shadow-card)] transition-transform hover:-translate-y-0.5"
        >
            <div class="aspect-[16/10] overflow-hidden bg-[var(--color-panel-soft)]">
                <img src="${item.url}" alt="${item.alt_text ?? ''}" class="h-full w-full object-cover">
            </div>
            <div class="space-y-2 px-4 py-4">
                <p class="truncate text-sm font-semibold text-[var(--color-ink)]">${item.original_filename}</p>
                <p class="truncate text-xs text-[var(--color-muted)]">${item.alt_text || 'No alt text'}</p>
            </div>
        </button>
    `).join('');
}

function openInlineMediaPicker(quill, element) {
    const modal = mediaPickerModal();
    const items = readMediaLibrary(element);

    modal.__inlineMediaContext = { quill, element, items };
    renderInlineMediaLibrary(modal, items);
    setInlineMediaTab(modal, 'library');
    setInlineMediaError(modal);

    const fileInput = modal.querySelector('[data-inline-media-file]');
    const altInput = modal.querySelector('[data-inline-media-alt]');
    const captionInput = modal.querySelector('[data-inline-media-caption]');

    if (fileInput) {
        fileInput.value = '';
    }

    if (altInput) {
        altInput.value = '';
    }

    if (captionInput) {
        captionInput.value = '';
    }

    modal.classList.remove('hidden');
}

function closeInlineMediaPicker(modal) {
    modal.classList.add('hidden');
    modal.__inlineMediaContext = null;
}

function installInlineMediaPickerEvents() {
    const modal = mediaPickerModal();

    if (modal.dataset.inlineMediaBound === 'true') {
        return;
    }

    modal.dataset.inlineMediaBound = 'true';

    modal.addEventListener('click', async (event) => {
        const closeTrigger = event.target.closest('[data-inline-media-close], [data-inline-media-overlay]');

        if (closeTrigger) {
            closeInlineMediaPicker(modal);

            return;
        }

        const tabTrigger = event.target.closest('[data-inline-media-tab]');

        if (tabTrigger) {
            setInlineMediaTab(modal, tabTrigger.getAttribute('data-inline-media-tab') ?? 'library');
            setInlineMediaError(modal);

            return;
        }

        const selectTrigger = event.target.closest('[data-select-inline-media-id]');

        if (selectTrigger && modal.__inlineMediaContext) {
            const mediaId = Number.parseInt(selectTrigger.getAttribute('data-select-inline-media-id') ?? '', 10);
            const media = modal.__inlineMediaContext.items.find((item) => item.id === mediaId);

            if (media) {
                insertMediaImage(modal.__inlineMediaContext.quill, media);
                closeInlineMediaPicker(modal);
            }

            return;
        }

        const uploadTrigger = event.target.closest('[data-inline-media-upload]');

        if (!uploadTrigger || !modal.__inlineMediaContext) {
            return;
        }

        const fileInput = modal.querySelector('[data-inline-media-file]');
        const altInput = modal.querySelector('[data-inline-media-alt]');
        const captionInput = modal.querySelector('[data-inline-media-caption]');
        const file = fileInput?.files?.[0] ?? null;

        if (!file) {
            setInlineMediaError(modal, 'Choose an image file before uploading.');

            return;
        }

        uploadTrigger.setAttribute('disabled', 'disabled');
        uploadTrigger.textContent = 'Uploading…';
        setInlineMediaError(modal);

        try {
            const media = await uploadInlineMedia(modal.__inlineMediaContext.element, file, {
                alt_text: altInput?.value?.trim() ?? '',
                caption: captionInput?.value?.trim() ?? '',
            });

            upsertMediaLibraryItem(modal.__inlineMediaContext.element, media);
            modal.__inlineMediaContext.items = readMediaLibrary(modal.__inlineMediaContext.element);
            insertMediaImage(modal.__inlineMediaContext.quill, media);
            closeInlineMediaPicker(modal);
        } catch (error) {
            setInlineMediaError(modal, error instanceof Error ? error.message : 'Inline media upload failed.');
        } finally {
            uploadTrigger.removeAttribute('disabled');
            uploadTrigger.textContent = 'Upload And Insert';
        }
    });
}

function bootQuillEditors() {
    document.querySelectorAll('[data-quill-editor]').forEach((element) => {
        if (element.dataset.quillReady === 'true') {
            return;
        }

        const htmlFieldSelector = element.dataset.quillHtmlField;
        const deltaFieldSelector = element.dataset.quillDeltaField;
        const initialHtml = readInitialHtml(element);

        const htmlField = htmlFieldSelector ? document.querySelector(htmlFieldSelector) : null;
        const deltaField = deltaFieldSelector ? document.querySelector(deltaFieldSelector) : null;

        const quill = new Quill(element, {
            modules: {
                toolbar,
            },
            placeholder: 'Write the article body here...',
            theme: 'snow',
        });

        quill.clipboard.addMatcher('IMG', (node, delta) => {
            const src = node.getAttribute('src') ?? '';
            const alt = node.getAttribute('alt') ?? '';
            const mediaId = node.getAttribute('data-media-id') ?? '';

            if (src.startsWith('data:image/')) {
                window.requestAnimationFrame(() => notifyImageEmbedPolicy());

                return new Delta();
            }

            return new Delta().insert({
                image: {
                    src,
                    alt,
                    mediaId,
                },
            });
        });

        const toolbarModule = quill.getModule('toolbar');

        toolbarModule.addHandler('image', () => {
            openInlineMediaPicker(quill, element);
        });

        preventInlineImageFiles(quill);

        if (initialHtml.trim() !== '') {
            quill.clipboard.dangerouslyPasteHTML(initialHtml);
        }

        const sync = () => {
            const html = normalizeHtml(quill.root.innerHTML);
            updateHiddenField(htmlField, html);
            updateHiddenField(deltaField, JSON.stringify(quill.getContents()));
        };

        quill.on('text-change', sync);
        sync();

        element.dataset.quillReady = 'true';
    });
}

function watchQuillEditorMounts() {
    if (window.__widewebblogQuillObserverStarted) {
        return;
    }

    window.__widewebblogQuillObserverStarted = true;

    const observer = new MutationObserver(() => {
        window.requestAnimationFrame(() => bootQuillEditors());
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

installInlineMediaPickerEvents();
document.addEventListener('DOMContentLoaded', bootQuillEditors);
document.addEventListener('livewire:init', bootQuillEditors);
document.addEventListener('livewire:navigated', bootQuillEditors);
document.addEventListener('DOMContentLoaded', watchQuillEditorMounts);

<template>
    <div class="page">
        <header class="site-header">
            <div class="container header-row">
                <a class="brand" href="/">Блог</a>
                <nav class="toolbar" role="toolbar" aria-label="Сортування">
                    <button
                        class="btn"
                        :class="{ 'btn-active': sortField === 'title' }"
                        @click="changeSort('title')"
                    >
                        Назва <span v-if="sortField === 'title'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                    <button
                        class="btn"
                        :class="{ 'btn-active': sortField === 'published_at' }"
                        @click="changeSort('published_at')"
                    >
                        Дата <span v-if="sortField === 'published_at'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                    </button>
                </nav>
            </div>
        </header>

        <main class="container">
            <h1 class="title">Останні публікації</h1>

            <div v-if="errorMessage" class="alert alert-error" role="alert">
                <strong>Помилка:</strong> {{ errorMessage }}
            </div>

            <div v-if="isLoading" class="loading" aria-live="polite">Завантаження…</div>

            <section v-else>
                <article v-for="article in articles" :key="article.id" class="card">
                    <div class="card-meta">
                        <time class="pill" :datetime="article.dateISO">{{ article.date }}</time>
                        <span class="muted ellipsis">{{ article.host }}</span>
                    </div>
                    <h2 class="card-title">
                        <a :href="article.url" target="_blank" rel="noopener">{{ article.title }}</a>
                    </h2>
                    <p class="card-actions">
                        <a class="link" :href="article.url" target="_blank" rel="noopener">Читати →</a>
                    </p>
                </article>

                <div v-if="!articles.length" class="empty">
                    Публікацій немає. Змініть сортування або оновіть сторінку.
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="container footer-row">
                <span>© {{ new Date().getFullYear() }} WebMagic Blog (demo)</span>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const sortField = ref('title')
const sortDirection = ref('asc')

const articles = ref([])
const isLoading = ref(false)
const errorMessage = ref('')

function formatDateToDMY(isoString) {
    if (!isoString || typeof isoString !== 'string') return ''
    const parts = isoString.split('-')
    if (parts.length !== 3) return ''
    const [year, month, day] = parts
    return `${day}.${month}.${year}`
}

function extractHostFromUrl(urlString) {
    try {
        const host = new URL(urlString).host
        return host.replace(/^www\./, '')
    } catch {
        return ''
    }
}

function readStateFromQueryString() {
    const params = new URLSearchParams(location.search)
    const sortFromQuery = params.get('sortBy')
    const directionFromQuery = params.get('direction')

    if (sortFromQuery === 'title' || sortFromQuery === 'published_at') {
        sortField.value = sortFromQuery
    }
    if (directionFromQuery === 'asc' || directionFromQuery === 'desc') {
        sortDirection.value = directionFromQuery
    }
}

function writeStateToQueryString() {
    const params = new URLSearchParams({
        sortBy: sortField.value,
        direction: sortDirection.value,
    })
    history.replaceState({}, '', `${location.pathname}?${params.toString()}`)
}

function normalizeList(json) {
    if (Array.isArray(json)) return json
    if (Array.isArray(json?.data)) return json.data
    if (Array.isArray(json?.data?.data)) return json.data.data
    return []
}

async function fetchArticles() {
    isLoading.value = true
    errorMessage.value = ''
    try {
        writeStateToQueryString()

        const params = new URLSearchParams({
            sortBy: sortField.value,
            direction: sortDirection.value,
        })

        const response = await fetch(`/api/articles?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        })
        if (!response.ok) throw new Error(`HTTP ${response.status}`)

        const json = await response.json()
        const list = normalizeList(json)

        articles.value = list.map(row => ({
            id: row.id,
            title: row.title,
            url: row.url,
            dateISO: row.published_at,
            date: formatDateToDMY(row.published_at),
            host: extractHostFromUrl(row.url),
        }))
    } catch (error) {
        errorMessage.value = error?.message || 'Не вдалося завантажити'
    } finally {
        isLoading.value = false
    }
}

function changeSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'asc'
    }
    fetchArticles()
}

onMounted(() => {
    readStateFromQueryString()
    fetchArticles()
})
</script>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #fff;
    color: #111;
}
.container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 16px;
}

.site-header,
.site-footer {
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
}

.header-row,
.footer-row {
    height: 56px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-row {
    justify-content: space-between;
    color: #555;
    font-size: 14px;
}

.brand {
    font-weight: 700;
    text-decoration: none;
    color: inherit;
}

.toolbar {
    margin-left: auto;
    display: flex;
    gap: 8px;
}

.title {
    font-size: 28px;
    margin: 20px 0 12px;
}

.alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin: 8px 0 12px;
    font-size: 14px;
}

.alert-error {
    background: #fde2e2;
    color: #7a1717;
    border: 1px solid #f5bcbc;
}

.loading {
    padding: 12px 0;
    color: #555;
}

.card {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
}

.card-meta {
    display: flex;
    gap: 8px;
    align-items: center;
    font-size: 12px;
    color: #555;
}

.card-title {
    margin: 8px 0 0;
    font-size: 18px;
}

.card-actions {
    margin: 8px 0 0;
}

.btn {
    border: 1px solid #ddd;
    background: #f8f8f8;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    min-width: 36px;
    height: 36px;
}

.btn-active {
    border-color: #2563eb;
    font-weight: 600;
}

.btn:disabled {
    opacity: 0.6;
    cursor: default;
}

.link {
    color: #2563eb;
    text-decoration: none;
}

.pill {
    padding: 2px 6px;
    border: 1px solid #ddd;
    border-radius: 999px;
}

.muted {
    color: #555;
}

.ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.site-footer {
    margin-top: auto;
}
</style>

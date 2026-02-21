// IndexedDB wrapper for offline PWA data persistence
const DB_NAME = 'plupro-offline';
const DB_VERSION = 1;

let dbInstance = null;

function openDB() {
    if (dbInstance) return Promise.resolve(dbInstance);

    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('lists')) {
                const listsStore = db.createObjectStore('lists', { keyPath: 'id' });
                listsStore.createIndex('name', 'name', { unique: false });
            }

            if (!db.objectStoreNames.contains('listItems')) {
                const itemsStore = db.createObjectStore('listItems', { keyPath: 'id' });
                itemsStore.createIndex('userListId', 'userListId', { unique: false });
            }

            if (!db.objectStoreNames.contains('meta')) {
                db.createObjectStore('meta', { keyPath: 'key' });
            }
        };

        request.onsuccess = () => {
            dbInstance = request.result;

            dbInstance.onclose = () => { dbInstance = null; };
            dbInstance.onversionchange = () => {
                dbInstance.close();
                dbInstance = null;
            };

            resolve(dbInstance);
        };

        request.onerror = () => reject(request.error);
    });
}

async function saveListItems(listId, items) {
    const db = await openDB();
    const tx = db.transaction('listItems', 'readwrite');
    const store = tx.objectStore('listItems');

    // Remove old items for this list first
    const index = store.index('userListId');
    const existingKeys = await idbGetAllKeys(index, listId);
    for (const key of existingKeys) {
        store.delete(key);
    }

    // Write new items
    for (const item of items) {
        store.put({ ...item, userListId: listId });
    }

    return idbTxComplete(tx);
}

async function getListItems(listId) {
    const db = await openDB();
    const tx = db.transaction('listItems', 'readonly');
    const index = tx.objectStore('listItems').index('userListId');
    return idbGetAll(index, listId);
}

async function updateListItem(item) {
    const db = await openDB();
    const tx = db.transaction('listItems', 'readwrite');
    tx.objectStore('listItems').put(item);
    return idbTxComplete(tx);
}

async function updateListItemInventory(listId, itemId, inventoryLevel) {
    const db = await openDB();
    const tx = db.transaction('listItems', 'readwrite');
    const store = tx.objectStore('listItems');

    const existing = await idbGet(store, itemId);
    if (existing) {
        existing.inventory_level = inventoryLevel;
        existing.lastModified = Date.now();
        store.put(existing);
    }

    return idbTxComplete(tx);
}

async function markItemsSynced(itemIds) {
    const db = await openDB();
    const tx = db.transaction('listItems', 'readwrite');
    const store = tx.objectStore('listItems');

    for (const id of itemIds) {
        const item = await idbGet(store, id);
        if (item && item.lastModified) {
            item.syncedAt = Date.now();
            delete item.lastModified;
            store.put(item);
        }
    }

    return idbTxComplete(tx);
}

async function saveUserLists(lists) {
    const db = await openDB();
    const tx = db.transaction('lists', 'readwrite');
    const store = tx.objectStore('lists');

    // Clear existing and write fresh
    store.clear();
    for (const list of lists) {
        store.put(list);
    }

    return idbTxComplete(tx);
}

async function getUserLists() {
    const db = await openDB();
    const tx = db.transaction('lists', 'readonly');
    return idbGetAll(tx.objectStore('lists'));
}

async function setMeta(key, value) {
    const db = await openDB();
    const tx = db.transaction('meta', 'readwrite');
    tx.objectStore('meta').put({ key, value });
    return idbTxComplete(tx);
}

async function getMeta(key) {
    const db = await openDB();
    const tx = db.transaction('meta', 'readonly');
    const result = await idbGet(tx.objectStore('meta'), key);
    return result ? result.value : null;
}

// --- IDB promise helpers ---

function idbGet(store, key) {
    return new Promise((resolve, reject) => {
        const req = store.get(key);
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function idbGetAll(storeOrIndex, query) {
    return new Promise((resolve, reject) => {
        const req = query !== undefined ? storeOrIndex.getAll(query) : storeOrIndex.getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => reject(req.error);
    });
}

function idbGetAllKeys(index, query) {
    return new Promise((resolve, reject) => {
        const req = index.getAllKeys(query);
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => reject(req.error);
    });
}

function idbTxComplete(tx) {
    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
        tx.onabort = () => reject(tx.error || new Error('Transaction aborted'));
    });
}

// Expose globally for use across components
window.OfflineDB = {
    openDB,
    saveListItems,
    getListItems,
    updateListItem,
    updateListItemInventory,
    markItemsSynced,
    saveUserLists,
    getUserLists,
    setMeta,
    getMeta,
};

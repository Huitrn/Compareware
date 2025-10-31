class TTLCache {
  constructor(defaultTtlMs = 10 * 60 * 1000) {
    this.store = new Map();
    this.defaultTtlMs = defaultTtlMs;
  }

  _now() {
    return Date.now();
  }

  set(key, value, ttlMs = this.defaultTtlMs) {
    const expiresAt = this._now() + ttlMs;
    this.store.set(key, { value, expiresAt });
  }

  get(key) {
    const entry = this.store.get(key);
    if (!entry) return undefined;
    if (this._now() > entry.expiresAt) {
      this.store.delete(key);
      return undefined;
    }
    return entry.value;
  }

  has(key) {
    return this.get(key) !== undefined;
  }

  delete(key) {
    this.store.delete(key);
  }

  clear() {
    this.store.clear();
  }
}

module.exports = TTLCache;

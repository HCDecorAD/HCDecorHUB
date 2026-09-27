/**
 * Canonical HCDecor Project ID.
 * Format: HC-YYYY-XXXX (example: HC-2026-0001).
 * IDs are immutable once assigned and must be reused by HUB, Drive, CMS and Agent.
 */
export const PROJECT_ID_PATTERN = /^HC-(\d{4})-(\d{4})$/;
export const LEGACY_PROJECT_ID_PATTERN = /^HC-(\d{2})(\d{3})$/;

export function formatProjectId(year, sequence) {
  const y = Number(year);
  const s = Number(sequence);
  if (!Number.isInteger(y) || y < 2000 || y > 9999) throw new Error("Invalid project year");
  if (!Number.isInteger(s) || s < 1 || s > 9999) throw new Error("Invalid project sequence");
  return `HC-${y}-${String(s).padStart(4, "0")}`;
}

export function isProjectId(value) {
  return PROJECT_ID_PATTERN.test(String(value || ""));
}

export function parseProjectId(value) {
  const match = String(value || "").match(PROJECT_ID_PATTERN);
  if (!match) return null;
  return { projectId: value, year: Number(match[1]), sequence: Number(match[2]) };
}

/**
 * Legacy IDs such as HC-26001 remain aliases; existing files/folders are not renamed.
 * HC-26001 => HC-2026-0001.
 */
export function migrateLegacyProjectId(value) {
  const raw = String(value || "").trim();
  if (isProjectId(raw)) return { projectId: raw, legacyProjectId: null, migrated: false };
  const match = raw.match(LEGACY_PROJECT_ID_PATTERN);
  if (!match) return null;
  const year = 2000 + Number(match[1]);
  const sequence = Number(match[2]);
  return {
    projectId: formatProjectId(year, sequence),
    legacyProjectId: raw,
    migrated: true,
  };
}

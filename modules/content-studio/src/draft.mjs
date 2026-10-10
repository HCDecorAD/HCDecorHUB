// M2 — Phân tích & viết nháp: dựng prompt có ràng buộc và kiểm tra đầu ra.
// Không tự gọi mô hình. Lớp gọi mô hình (Ollama local) được cắm ở bước sau.

export const FORBIDDEN_PATTERNS = [
  /\bgiảm\s*\d+\s*%/i,          // khuyến mãi bịa
  /\b\d[\d.,]*\s*(đ|vnđ|vnd|usd)\b/i, // giá bịa
  /\bbảo\s*hành\s*\d+\s*năm\b/i, // cam kết bịa
  /\bsố\s*1\b|\bnumber\s*one\b/i, // so sánh tuyệt đối không có căn cứ
];

export function buildPrompt(brief, brandProfile = {}) {
  const rules = [
    'Chỉ dùng thông tin có trong brief và hồ sơ thương hiệu.',
    'Không tự thêm giá, khuyến mãi, số liệu, cam kết nếu brief không có.',
    'Viết bằng tiếng Việt, giọng thân thiện, ngắn gọn.',
    `Tạo đúng ${brief.count} phương án.`,
    'Trả về JSON: [{title, body, cta, media_hint}].',
  ];
  return [
    'Bạn là người viết nội dung cho thương hiệu.',
    `Chủ đề: ${brief.topic}`,
    `Kênh đích: ${brief.channels.join(', ')}`,
    `Hồ sơ thương hiệu: ${JSON.stringify(brandProfile)}`,
    'Quy tắc:',
    ...rules.map((r) => `- ${r}`),
  ].join('\n');
}

export function checkDraft(draft) {
  const problems = [];
  const text = `${draft.title ?? ''} ${draft.body ?? ''} ${draft.cta ?? ''}`;
  for (const p of FORBIDDEN_PATTERNS) {
    if (p.test(text)) problems.push({ code: 'UNSUPPORTED_CLAIM', pattern: String(p) });
  }
  if (!draft.title) problems.push({ code: 'MISSING_TITLE' });
  if (!draft.body) problems.push({ code: 'MISSING_BODY' });
  return { ok: problems.length === 0, problems };
}

export function requireApproval(draft) {
  return { ...draft, status: 'pending_approval', production_approved: false };
}

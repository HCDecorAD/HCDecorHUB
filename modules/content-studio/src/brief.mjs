// M1 — Cửa nhận lệnh: biến lệnh tự nhiên thành brief có cấu trúc.
// Không gọi mạng, không ghi file. Hàm thuần để dễ kiểm thử.

const CHANNELS = ['facebook', 'tiktok', 'youtube', 'zalo', 'instagram'];

export function parseCommand(text) {
  const raw = String(text ?? '').trim();
  if (!raw) return { ok: false, error: 'EMPTY_COMMAND' };

  const countMatch = raw.match(/(\d{1,2})\s*(bài|post|posts)/i);
  const count = countMatch ? Math.min(Math.max(parseInt(countMatch[1], 10), 1), 10) : 2;

  const channels = CHANNELS.filter((c) => new RegExp(`\\b${c}\\b`, 'i').test(raw));

  const hasDate = /\b(\d{1,2}[\/\-]\d{1,2}|ngày mai|tuần sau|hôm nay)\b/i.test(raw);

  const brief = {
    schema_version: '0.1.0',
    raw_command: raw,
    count,
    channels: channels.length ? channels : ['facebook'],
    topic: raw
      .replace(/(\d{1,2})\s*(bài|post|posts)/gi, '')
      .replace(/\b(facebook|tiktok|youtube|zalo|instagram)\b/gi, '')
      .replace(/\s+/g, ' ')
      .trim(),
    schedule_hint: hasDate ? 'user_specified' : 'unspecified',
    approval_required: true,
    publish_enabled: false,
  };

  if (!brief.topic) return { ok: false, error: 'NO_TOPIC', brief };
  return { ok: true, brief };
}

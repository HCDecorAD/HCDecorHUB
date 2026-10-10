import test from 'node:test';
import assert from 'node:assert/strict';
import { parseCommand } from '../src/brief.mjs';
import { buildPrompt, checkDraft, requireApproval } from '../src/draft.mjs';

test('M1: lệnh rỗng bị từ chối', () => {
  assert.equal(parseCommand('   ').ok, false);
});

test('M1: đọc số bài và kênh', () => {
  const r = parseCommand('3 bài về bảng hiệu LED trên facebook và tiktok');
  assert.equal(r.ok, true);
  assert.equal(r.brief.count, 3);
  assert.deepEqual(r.brief.channels, ['facebook', 'tiktok']);
  assert.equal(r.brief.publish_enabled, false);
  assert.equal(r.brief.approval_required, true);
});

test('M1: số bài bị giới hạn 1–10', () => {
  assert.equal(parseCommand('50 bài về nội thất').brief.count, 10);
});

test('M2: prompt chứa ràng buộc không bịa', () => {
  const { brief } = parseCommand('2 bài về thi công bảng hiệu');
  const p = buildPrompt(brief, { brand: 'HCDecor' });
  assert.match(p, /Không tự thêm giá/);
  assert.match(p, /Tạo đúng 2 phương án/);
});

test('M2: bắt được khuyến mãi và giá bịa', () => {
  const r = checkDraft({ title: 'Giảm 30% hôm nay', body: 'Giá chỉ 500.000đ', cta: '' });
  assert.equal(r.ok, false);
  assert.ok(r.problems.some((p) => p.code === 'UNSUPPORTED_CLAIM'));
});

test('M2: nháp hợp lệ qua kiểm tra', () => {
  const r = checkDraft({ title: 'Bảng hiệu HCDecor', body: 'Thiết kế và thi công bảng hiệu.', cta: 'Liên hệ' });
  assert.equal(r.ok, true);
});

test('M2: nháp luôn chờ duyệt, không tự đăng', () => {
  const d = requireApproval({ title: 'x', body: 'y' });
  assert.equal(d.status, 'pending_approval');
  assert.equal(d.production_approved, false);
});

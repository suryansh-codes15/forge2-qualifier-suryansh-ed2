import { useState } from "react";

const TAG_COLORS = ["#e74c3c", "#e67e22", "#f1c40f", "#2ecc71", "#3498db", "#9b59b6"];

export default function CardModal({ card, members, allTags, onClose, onUpdate, onDelete, onAttachTag, onDetachTag, onAssign, onUnassign }) {
  const [title, setTitle] = useState(card.title);
  const [description, setDescription] = useState(card.description || "");
  const [dueDate, setDueDate] = useState(card.due_date ? card.due_date.slice(0, 10) : "");

  const cardTagIds = new Set((card.tags || []).map((t) => t.id));
  const cardMemberIds = new Set((card.members || []).map((m) => m.id));

  function save() {
    onUpdate(card.id, { title, description, due_date: dueDate || null });
  }

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <button className="modal-close" onClick={onClose}>&times;</button>

        <label className="field-label">Title</label>
        <input value={title} onChange={(e) => setTitle(e.target.value)} onBlur={save} />

        <label className="field-label">Description</label>
        <textarea value={description} onChange={(e) => setDescription(e.target.value)} onBlur={save} rows={4} />

        <label className="field-label">Due date</label>
        <input type="date" value={dueDate} onChange={(e) => setDueDate(e.target.value)} onBlur={save} />

        <label className="field-label">Tags</label>
        <div className="tag-row">
          {allTags.map((tag) => {
            const active = cardTagIds.has(tag.id);
            return (
              <button
                key={tag.id}
                className={`tag-chip ${active ? "active" : ""}`}
                style={{ background: tag.color }}
                onClick={() => (active ? onDetachTag(card.id, tag.id) : onAttachTag(card.id, tag.id))}
              >
                {tag.name}
              </button>
            );
          })}
        </div>

        <label className="field-label">Members</label>
        <div className="member-row">
          {members.map((m) => {
            const active = cardMemberIds.has(m.id);
            return (
              <button
                key={m.id}
                className={`member-chip ${active ? "active" : ""}`}
                onClick={() => (active ? onUnassign(card.id, m.id) : onAssign(card.id, m.id))}
              >
                {m.name}
              </button>
            );
          })}
        </div>

        <button className="danger-btn" onClick={() => { onDelete(card.id); onClose(); }}>
          Delete card
        </button>
      </div>
    </div>
  );
}

export { TAG_COLORS };

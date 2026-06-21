import { useEffect, useState, useCallback } from "react";
import { api } from "./api";
import CardModal from "./CardModal";

function isOverdue(card) {
  if (!card.due_date) return false;
  const due = new Date(card.due_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return due < today;
}

export default function App() {
  const [boards, setBoards] = useState([]);
  const [activeBoard, setActiveBoard] = useState(null);
  const [members, setMembers] = useState([]);
  const [tags, setTags] = useState([]);
  const [openCard, setOpenCard] = useState(null);
  const [error, setError] = useState(null);
  const [newListName, setNewListName] = useState("");
  const [newCardTitle, setNewCardTitle] = useState({});

  const loadBoard = useCallback(async (boardId) => {
    try {
      const board = await api.getBoard(boardId);
      setActiveBoard(board);
    } catch (e) {
      setError(e.message);
    }
  }, []);

  useEffect(() => {
    (async () => {
      try {
        const [b, m, t] = await Promise.all([api.getBoards(), api.getMembers(), api.getTags()]);
        setBoards(b);
        setMembers(m);
        setTags(t);
        if (b.length) loadBoard(b[0].id);
      } catch (e) {
        setError(e.message);
      }
    })();
  }, [loadBoard]);

  async function createBoard() {
    const name = prompt("Board name?");
    if (!name) return;
    const board = await api.createBoard({ name });
    setBoards((prev) => [...prev, board]);
    loadBoard(board.id);
  }

  async function addList() {
    if (!newListName.trim() || !activeBoard) return;
    await api.createList(activeBoard.id, { name: newListName.trim() });
    setNewListName("");
    loadBoard(activeBoard.id);
  }

  async function addCard(listId) {
    const title = (newCardTitle[listId] || "").trim();
    if (!title) return;
    await api.createCard(listId, { title });
    setNewCardTitle((prev) => ({ ...prev, [listId]: "" }));
    loadBoard(activeBoard.id);
  }

  async function moveCard(cardId, toListId) {
    await api.moveCard(cardId, toListId);
    loadBoard(activeBoard.id);
  }

  async function updateCard(cardId, data) {
    await api.updateCard(cardId, data);
    loadBoard(activeBoard.id);
  }

  async function deleteCard(cardId) {
    await api.deleteCard(cardId);
    loadBoard(activeBoard.id);
  }

  async function attachTag(cardId, tagId) {
    await api.attachTag(cardId, tagId);
    loadBoard(activeBoard.id);
  }
  async function detachTag(cardId, tagId) {
    await api.detachTag(cardId, tagId);
    loadBoard(activeBoard.id);
  }
  async function assignMember(cardId, memberId) {
    await api.assignMember(cardId, memberId);
    loadBoard(activeBoard.id);
  }
  async function unassignMember(cardId, memberId) {
    await api.unassignMember(cardId, memberId);
    loadBoard(activeBoard.id);
  }

  function onDragStart(e, cardId) {
    e.dataTransfer.setData("cardId", cardId);
  }
  function onDrop(e, listId) {
    const cardId = e.dataTransfer.getData("cardId");
    if (cardId) moveCard(Number(cardId), listId);
  }

  return (
    <div className="app">
      <header className="topbar">
        <h1>Tiny Kanban</h1>
        <div className="board-tabs">
          {boards.map((b) => (
            <button
              key={b.id}
              className={`board-tab ${activeBoard?.id === b.id ? "active" : ""}`}
              onClick={() => loadBoard(b.id)}
            >
              {b.name}
            </button>
          ))}
          <button className="board-tab new" onClick={createBoard}>+ Board</button>
        </div>
      </header>

      {error && <div className="error-banner">{error}</div>}

      {activeBoard && (
        <main className="board">
          {activeBoard.lists.map((list) => (
            <div
              key={list.id}
              className="list"
              onDragOver={(e) => e.preventDefault()}
              onDrop={(e) => onDrop(e, list.id)}
            >
              <h3>{list.name}</h3>
              <div className="cards">
                {list.cards.map((card) => (
                  <div
                    key={card.id}
                    className={`card ${isOverdue(card) ? "overdue" : ""}`}
                    draggable
                    onDragStart={(e) => onDragStart(e, card.id)}
                    onClick={() => setOpenCard(card)}
                  >
                    <div className="card-title">{card.title}</div>
                    {card.tags?.length > 0 && (
                      <div className="card-tags">
                        {card.tags.map((t) => (
                          <span key={t.id} className="mini-tag" style={{ background: t.color }} />
                        ))}
                      </div>
                    )}
                    {card.due_date && (
                      <div className={`card-due ${isOverdue(card) ? "overdue-text" : ""}`}>
                        Due {card.due_date.slice(0, 10)}
                      </div>
                    )}
                    {card.members?.length > 0 && (
                      <div className="card-members">
                        {card.members.map((m) => m.name).join(", ")}
                      </div>
                    )}
                  </div>
                ))}
              </div>
              <div className="add-card-row">
                <input
                  placeholder="New card..."
                  value={newCardTitle[list.id] || ""}
                  onChange={(e) => setNewCardTitle((p) => ({ ...p, [list.id]: e.target.value }))}
                  onKeyDown={(e) => e.key === "Enter" && addCard(list.id)}
                />
                <button onClick={() => addCard(list.id)}>Add</button>
              </div>
            </div>
          ))}

          <div className="list new-list">
            <input
              placeholder="New list name..."
              value={newListName}
              onChange={(e) => setNewListName(e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && addList()}
            />
            <button onClick={addList}>Add list</button>
          </div>
        </main>
      )}

      {openCard && (
        <CardModal
          card={activeBoard?.lists.flatMap((l) => l.cards).find((c) => c.id === openCard.id) || openCard}
          members={members}
          allTags={tags}
          onClose={() => setOpenCard(null)}
          onUpdate={updateCard}
          onDelete={deleteCard}
          onAttachTag={attachTag}
          onDetachTag={detachTag}
          onAssign={assignMember}
          onUnassign={unassignMember}
        />
      )}
    </div>
  );
}

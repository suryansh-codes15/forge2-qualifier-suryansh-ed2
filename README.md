# Forge 2 Qualifier Kanban Board

A tiny Trello-style Kanban board built during the Forge 2 online qualifier using a cooperative two-agent system: Hermes as the brain and OpenClaw as the hands, wired through Slack.

### 🌐 Live Deployment
*   **Frontend (Vercel):** [https://nmg-kanban-board.vercel.app](https://nmg-kanban-board.vercel.app)
*   **Backend API (Localtunnel):** [https://nmg-forge-suryansh-v2.loca.lt](https://nmg-forge-suryansh-v2.loca.lt)

---


## Features

- Boards, lists, and cards with create flows for each level.
- Drag-and-drop card movement between lists.
- Card detail editing for title, description, and due date.
- Colored tags that can be attached to and detached from cards.
- Member assignment for cards.
- Overdue visual flags for cards past their due date.

## Agent System & Model Routing

- Orchestrator: Hermes Agent powered by Google Gemini 2.5 Flash through the direct Google AI Studio API for planning, memory retention, and workflow orchestration.
- Coder: OpenClaw powered by Google Gemini 2.5 Flash (via OpenAI compatibility) to handle large tool schema loads and bypass Groq free-tier TPM rate limits.
- Communication: Slack socket mode channels for planning, coding tasks, and audit trails.

## Project Structure

```text
/backend       Laravel API with SQLite
/frontend      React and Vite frontend
/skills        Reusable agent skills
/slack-export  Evidence logs of Slack wiring and tests
```

## Running the App Locally

### Prerequisites

- PHP 8.2+
- Node.js 22.19+ or newer
- Composer

### Start the Laravel Backend

1. Navigate to `backend`.
2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

4. Generate the application key, migrate, and seed the database:

   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```

5. Start the server:

   ```bash
   php artisan serve --port=8000
   ```

The API will be available at `http://localhost:8000`.

### Start the React Frontend

1. Navigate to `frontend`.
2. Install dependencies:

   ```bash
   npm install
   ```

3. Copy the environment file:

   ```bash
   cp .env.example .env
   ```

4. Start the Vite dev server:

   ```bash
   npm run dev
   ```

The UI will be available at `http://localhost:5173`.

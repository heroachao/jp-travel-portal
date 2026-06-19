const gameScoreEvent = (slug, action, params = {}) => {
    if (typeof window.gtag !== 'function') {
        return;
    }

    window.gtag('event', 'japan_game_interaction', {
        page_path: window.location.pathname,
        game_slug: slug,
        game_action: action,
        ...params,
    });
};

const gameClear = (node) => {
    while (node.firstChild) {
        node.removeChild(node.firstChild);
    }
};

const gameButton = (label, onClick, secondary = false) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `game-action-button${secondary ? ' is-secondary' : ''}`;
    button.textContent = label;
    button.addEventListener('click', onClick);

    return button;
};

const gameShuffle = (items) => {
    const shuffled = [...items];

    for (let index = shuffled.length - 1; index > 0; index -= 1) {
        const swapIndex = Math.floor(Math.random() * (index + 1));
        [shuffled[index], shuffled[swapIndex]] = [shuffled[swapIndex], shuffled[index]];
    }

    return shuffled;
};

const gameSample = (items) => items[Math.floor(Math.random() * items.length)];

const gameBestKey = (slug) => `jtt.game.${slug}.best`;

const buildGameContext = (root) => {
    const slug = root.dataset.japanGame;
    const configNode = root.querySelector('[data-game-config]');
    const config = JSON.parse(configNode?.textContent || '{}');
    const stage = root.querySelector('[data-game-stage]');
    const controls = root.querySelector('[data-game-controls]');
    const scoreNode = root.querySelector('[data-game-score]');
    const bestNode = root.querySelector('[data-game-best]');
    const stateNode = root.querySelector('[data-game-state]');
    const key = gameBestKey(slug);
    let best = Number(window.localStorage?.getItem(key) || 0);

    const setScore = (value) => {
        const score = Math.max(0, Math.round(Number(value) || 0));
        scoreNode.textContent = String(score);

        if (score > best) {
            best = score;
            bestNode.textContent = String(best);
            window.localStorage?.setItem(key, String(best));
        }

        return score;
    };

    bestNode.textContent = String(best);

    return {
        root,
        slug,
        config,
        stage,
        controls,
        setScore,
        setStatus: (status) => {
            stateNode.textContent = status;
        },
        track: (action, params = {}) => gameScoreEvent(slug, action, params),
        clear() {
            gameClear(stage);
            gameClear(controls);
            setScore(0);
            this.setStatus('Ready');
        },
    };
};

const buildDailyWordGame = (ctx) => {
    const words = ['TOKYO', 'KYOTO', 'OSAKA', 'NIKKO', 'SUSHI', 'RAMEN', 'ONSEN', 'KOBAN', 'KENDO', 'TRAIN'];
    const target = words[Math.floor(Date.now() / 86400000) % words.length];
    const guesses = [];
    let complete = false;

    const grid = document.createElement('div');
    grid.className = 'word-grid';
    const entry = document.createElement('form');
    entry.className = 'word-entry';
    const input = document.createElement('input');
    input.maxLength = 5;
    input.autocapitalize = 'characters';
    input.autocomplete = 'off';
    input.placeholder = 'Type five letters';
    const submit = gameButton('Guess', () => {});
    entry.append(input, submit);

    const scoreGuess = (word) => [...word].map((letter, index) => {
        if (target[index] === letter) {
            return 'is-hit';
        }

        return target.includes(letter) ? 'is-present' : 'is-miss';
    });

    const render = () => {
        gameClear(grid);

        for (let rowIndex = 0; rowIndex < 6; rowIndex += 1) {
            const row = document.createElement('div');
            row.className = 'word-row';
            const guess = guesses[rowIndex];
            const classes = guess ? scoreGuess(guess) : [];

            for (let column = 0; column < 5; column += 1) {
                const letter = document.createElement('span');
                letter.className = `word-letter ${classes[column] || ''}`;
                letter.textContent = guess?.[column] || '';
                row.appendChild(letter);
            }

            grid.appendChild(row);
        }
    };

    entry.addEventListener('submit', (event) => {
        event.preventDefault();

        if (complete) {
            return;
        }

        const guess = input.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 5);

        if (guess.length !== 5) {
            ctx.setStatus('Five letters');
            input.value = guess;
            return;
        }

        guesses.push(guess);
        input.value = '';
        render();
        ctx.track('guess', { game_score: guesses.length });

        if (guess === target) {
            complete = true;
            const score = ctx.setScore((7 - guesses.length) * 100);
            ctx.setStatus('Won');
            ctx.track('complete', { game_result: 'win', game_score: score });
            input.disabled = true;
            return;
        }

        if (guesses.length >= 6) {
            complete = true;
            ctx.setStatus(target);
            ctx.track('complete', { game_result: 'loss', game_score: 0 });
            input.disabled = true;
            return;
        }

        ctx.setStatus(`${6 - guesses.length} tries`);
    });

    const reset = () => {
        guesses.splice(0, guesses.length);
        complete = false;
        input.disabled = false;
        input.value = '';
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
        input.focus();
        ctx.track('start');
    };

    ctx.clear();
    ctx.stage.append(grid, entry);
    ctx.controls.appendChild(gameButton('New round', reset, true));
    reset();
};

const buildFujiMergeGame = (ctx) => {
    const size = 4;
    let tiles = [];
    let score = 0;
    let won = false;

    const board = document.createElement('div');
    board.className = 'game-board merge-board';

    const emptyCells = () => tiles
        .map((value, index) => (value === 0 ? index : null))
        .filter((value) => value !== null);

    const addTile = () => {
        const empties = emptyCells();
        if (empties.length === 0) return;
        tiles[gameSample(empties)] = Math.random() < 0.88 ? 2 : 4;
    };

    const mergeValues = (values) => {
        const compact = values.filter(Boolean);
        const merged = [];
        let points = 0;

        for (let index = 0; index < compact.length; index += 1) {
            if (compact[index] === compact[index + 1]) {
                const value = compact[index] * 2;
                merged.push(value);
                points += value;
                index += 1;
            } else {
                merged.push(compact[index]);
            }
        }

        while (merged.length < size) merged.push(0);

        return { merged, points };
    };

    const linesFor = (direction) => {
        const lines = [];

        for (let offset = 0; offset < size; offset += 1) {
            const line = [];

            for (let step = 0; step < size; step += 1) {
                const x = direction === 'right' ? size - 1 - step : direction === 'left' ? step : offset;
                const y = direction === 'down' ? size - 1 - step : direction === 'up' ? step : offset;
                line.push(y * size + x);
            }

            lines.push(line);
        }

        return lines;
    };

    const hasMoves = () => {
        if (emptyCells().length > 0) return true;

        return tiles.some((value, index) => {
            const x = index % size;
            const y = Math.floor(index / size);

            return (x < size - 1 && tiles[index + 1] === value)
                || (y < size - 1 && tiles[index + size] === value);
        });
    };

    const render = () => {
        gameClear(board);
        tiles.forEach((value) => {
            const cell = document.createElement('div');
            cell.className = 'game-tile';
            cell.style.setProperty('--tile-strength', String(value ? Math.log2(value) : 1));
            cell.textContent = value ? String(value) : '';
            board.appendChild(cell);
        });
    };

    const move = (direction) => {
        let changed = false;
        let gained = 0;

        linesFor(direction).forEach((line) => {
            const before = line.map((index) => tiles[index]);
            const { merged, points } = mergeValues(before);
            gained += points;

            merged.forEach((value, offset) => {
                if (tiles[line[offset]] !== value) changed = true;
                tiles[line[offset]] = value;
            });
        });

        if (! changed) return;

        score += gained;
        addTile();
        render();
        ctx.setScore(score);
        ctx.track('move', { game_score: score });

        if (! won && Math.max(...tiles) >= 2048) {
            won = true;
            ctx.setStatus('Won');
            ctx.track('complete', { game_result: 'win', game_score: score });
            return;
        }

        if (! hasMoves()) {
            ctx.setStatus('Done');
            ctx.track('complete', { game_result: 'loss', game_score: score });
        }
    };

    const reset = () => {
        tiles = Array(size * size).fill(0);
        score = 0;
        won = false;
        addTile();
        addTile();
        render();
        ctx.setScore(0);
        ctx.setStatus('Ready');
        ctx.track('start');
    };

    const keydown = (event) => {
        const direction = {
            ArrowLeft: 'left',
            ArrowRight: 'right',
            ArrowUp: 'up',
            ArrowDown: 'down',
        }[event.key];

        if (! direction) return;
        event.preventDefault();
        move(direction);
    };

    ctx.clear();
    ctx.stage.appendChild(board);
    ['up', 'left', 'down', 'right'].forEach((direction) => {
        ctx.controls.appendChild(gameButton(direction, () => move(direction), direction !== 'up'));
    });
    ctx.controls.appendChild(gameButton('New board', reset, true));
    document.addEventListener('keydown', keydown);
    reset();
};

const buildSushiSnakeGame = (ctx) => {
    const size = 14;
    let snake;
    let food;
    let direction;
    let nextDirection;
    let timer = null;
    let alive = true;
    const board = document.createElement('div');
    board.className = 'game-board snake-board';

    const same = (a, b) => a.x === b.x && a.y === b.y;
    const randomFood = () => {
        let next;
        do {
            next = { x: Math.floor(Math.random() * size), y: Math.floor(Math.random() * size) };
        } while (snake.some((part) => same(part, next)));
        return next;
    };

    const render = () => {
        gameClear(board);
        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                const cell = document.createElement('span');
                const point = { x, y };
                cell.className = `game-cell${snake.some((part) => same(part, point)) ? ' is-snake' : ''}${same(food, point) ? ' is-food' : ''}`;
                board.appendChild(cell);
            }
        }
    };

    const stop = (status) => {
        window.clearInterval(timer);
        timer = null;
        alive = false;
        ctx.setStatus(status);
        ctx.track('complete', { game_result: 'loss', game_score: snake.length - 3 });
    };

    const tick = () => {
        direction = nextDirection;
        const head = snake[0];
        const next = { x: head.x + direction.x, y: head.y + direction.y };

        if (next.x < 0 || next.y < 0 || next.x >= size || next.y >= size || snake.some((part) => same(part, next))) {
            stop('Done');
            render();
            return;
        }

        snake.unshift(next);

        if (same(next, food)) {
            food = randomFood();
            ctx.setScore((snake.length - 3) * 10);
        } else {
            snake.pop();
        }

        render();
    };

    const setDirection = (next) => {
        if (next.x + direction.x === 0 && next.y + direction.y === 0) return;
        nextDirection = next;
    };

    const start = () => {
        if (timer || ! alive) return;
        ctx.setStatus('Playing');
        ctx.track('start');
        timer = window.setInterval(tick, 150);
    };

    const reset = () => {
        window.clearInterval(timer);
        timer = null;
        snake = [{ x: 6, y: 7 }, { x: 5, y: 7 }, { x: 4, y: 7 }];
        direction = { x: 1, y: 0 };
        nextDirection = direction;
        alive = true;
        food = randomFood();
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
    };

    document.addEventListener('keydown', (event) => {
        const next = {
            ArrowLeft: { x: -1, y: 0 },
            ArrowRight: { x: 1, y: 0 },
            ArrowUp: { x: 0, y: -1 },
            ArrowDown: { x: 0, y: 1 },
        }[event.key];
        if (! next) return;
        event.preventDefault();
        setDirection(next);
        start();
    });

    ctx.clear();
    ctx.stage.appendChild(board);
    ctx.controls.append(
        gameButton('Start', start),
        gameButton('Up', () => setDirection({ x: 0, y: -1 }), true),
        gameButton('Left', () => setDirection({ x: -1, y: 0 }), true),
        gameButton('Right', () => setDirection({ x: 1, y: 0 }), true),
        gameButton('Down', () => setDirection({ x: 0, y: 1 }), true),
        gameButton('Restart', reset, true),
    );
    reset();
};

const buildToriiMemoryGame = (ctx) => {
    const labels = ['Fuji', 'Torii', 'Ramen', 'Onsen', 'Rail', 'Castle', 'Sakura', 'Market'];
    let cards = [];
    let open = [];
    let locked = false;
    let moves = 0;
    const board = document.createElement('div');
    board.className = 'game-board memory-board';

    const render = () => {
        gameClear(board);
        cards.forEach((card, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `game-card-button${card.open || card.matched ? ' is-open' : ''}${card.matched ? ' is-matched' : ''}`;
            button.textContent = card.open || card.matched ? card.label : 'Japan';
            button.disabled = locked || card.open || card.matched;
            button.addEventListener('click', () => flip(index));
            board.appendChild(button);
        });
    };

    const flip = (index) => {
        if (locked || cards[index].matched || cards[index].open) return;
        cards[index].open = true;
        open.push(index);
        render();

        if (open.length < 2) return;

        moves += 1;
        const [first, second] = open;

        if (cards[first].label === cards[second].label) {
            cards[first].matched = true;
            cards[second].matched = true;
            open = [];
            const matched = cards.filter((card) => card.matched).length / 2;
            const score = ctx.setScore(Math.max(0, matched * 120 - moves * 5));
            ctx.setStatus(`${moves} moves`);
            ctx.track('match', { game_score: score });

            if (cards.every((card) => card.matched)) {
                ctx.setStatus('Won');
                ctx.track('complete', { game_result: 'win', game_score: score });
            }
            render();
            return;
        }

        locked = true;
        window.setTimeout(() => {
            cards[first].open = false;
            cards[second].open = false;
            open = [];
            locked = false;
            ctx.setStatus(`${moves} moves`);
            render();
        }, 700);
    };

    const reset = () => {
        cards = gameShuffle([...labels, ...labels]).map((label) => ({ label, open: false, matched: false }));
        open = [];
        moves = 0;
        locked = false;
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
        ctx.track('start');
    };

    ctx.clear();
    ctx.stage.appendChild(board);
    ctx.controls.appendChild(gameButton('New deck', reset, true));
    reset();
};

const buildSakuraMinesweeperGame = (ctx) => {
    const size = 8;
    const mineCount = 10;
    let cells = [];
    let ended = false;
    const board = document.createElement('div');
    board.className = 'game-board mine-board';

    const neighbors = (index) => {
        const x = index % size;
        const y = Math.floor(index / size);
        const results = [];

        for (let dy = -1; dy <= 1; dy += 1) {
            for (let dx = -1; dx <= 1; dx += 1) {
                if (dx === 0 && dy === 0) continue;
                const nx = x + dx;
                const ny = y + dy;
                if (nx >= 0 && ny >= 0 && nx < size && ny < size) {
                    results.push(ny * size + nx);
                }
            }
        }

        return results;
    };

    const reveal = (index) => {
        const cell = cells[index];
        if (! cell || cell.open || cell.flagged || ended) return;
        cell.open = true;

        if (cell.mine) {
            ended = true;
            cells.forEach((item) => { item.open = true; });
            ctx.setStatus('Storm');
            ctx.track('complete', { game_result: 'loss', game_score: Number(ctx.root.querySelector('[data-game-score]')?.textContent || 0) });
            render();
            return;
        }

        if (cell.count === 0) {
            neighbors(index).forEach(reveal);
        }

        const revealed = cells.filter((item) => item.open && ! item.mine).length;
        const score = ctx.setScore(revealed * 10);

        if (revealed === size * size - mineCount) {
            ended = true;
            ctx.setStatus('Won');
            ctx.track('complete', { game_result: 'win', game_score: score });
        }

        render();
    };

    const toggleFlag = (event, index) => {
        event.preventDefault();
        if (cells[index].open || ended) return;
        cells[index].flagged = ! cells[index].flagged;
        render();
    };

    const render = () => {
        gameClear(board);
        cells.forEach((cell, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `game-cell${cell.open ? ' is-open' : ''}${cell.open && cell.mine ? ' is-boom' : ''}`;
            button.textContent = cell.open ? (cell.mine ? 'X' : (cell.count || '')) : (cell.flagged ? 'Flag' : '');
            button.addEventListener('click', () => reveal(index));
            button.addEventListener('contextmenu', (event) => toggleFlag(event, index));
            board.appendChild(button);
        });
    };

    const reset = () => {
        const mines = new Set(gameShuffle(Array.from({ length: size * size }, (_, index) => index)).slice(0, mineCount));
        cells = Array.from({ length: size * size }, (_, index) => ({
            mine: mines.has(index),
            open: false,
            flagged: false,
            count: 0,
        }));
        cells.forEach((cell, index) => {
            cell.count = neighbors(index).filter((neighbor) => cells[neighbor].mine).length;
        });
        ended = false;
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
        ctx.track('start');
    };

    ctx.clear();
    ctx.stage.appendChild(board);
    ctx.controls.appendChild(gameButton('New picnic', reset, true));
    reset();
};

const buildTokyoMetroDashGame = (ctx) => {
    let running = false;
    let ended = false;
    let playerY = 0;
    let velocity = 0;
    let obstacleX = 520;
    let distance = 0;
    let last = 0;
    let frame = 0;
    const field = document.createElement('div');
    field.className = 'runner-field';
    const player = document.createElement('div');
    player.className = 'runner-player';
    player.textContent = 'GO';
    const obstacle = document.createElement('div');
    obstacle.className = 'runner-obstacle';
    obstacle.textContent = 'Gate';
    field.append(player, obstacle);

    const render = () => {
        player.style.transform = `translateY(${-playerY}px)`;
        obstacle.style.left = `${obstacleX}px`;
    };

    const jump = () => {
        if (! running) start();
        if (playerY <= 1) velocity = 15;
    };

    const end = () => {
        running = false;
        ended = true;
        window.cancelAnimationFrame(frame);
        ctx.setStatus('Done');
        ctx.track('complete', { game_result: 'loss', game_score: Math.round(distance) });
    };

    const loop = (time) => {
        if (! running) return;
        const delta = Math.min(32, time - last || 16);
        last = time;
        velocity -= 0.75;
        playerY = Math.max(0, playerY + velocity);
        if (playerY === 0 && velocity < 0) velocity = 0;
        obstacleX -= 0.24 * delta + Math.min(6, distance / 1200);

        if (obstacleX < -42) {
            obstacleX = field.clientWidth + Math.random() * 180;
        }

        distance += delta / 45;
        ctx.setScore(distance);

        if (obstacleX > 55 && obstacleX < 112 && playerY < 46) {
            end();
            render();
            return;
        }

        render();
        frame = window.requestAnimationFrame(loop);
    };

    const start = () => {
        if (running || ended) return;
        running = true;
        last = 0;
        ctx.setStatus('Running');
        ctx.track('start');
        frame = window.requestAnimationFrame(loop);
    };

    const reset = () => {
        window.cancelAnimationFrame(frame);
        running = false;
        ended = false;
        playerY = 0;
        velocity = 0;
        obstacleX = 520;
        distance = 0;
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
    };

    document.addEventListener('keydown', (event) => {
        if (event.code === 'Space' || event.key === 'ArrowUp') {
            event.preventDefault();
            jump();
        }
    });

    ctx.clear();
    ctx.stage.appendChild(field);
    ctx.controls.append(gameButton('Start', start), gameButton('Jump', jump, true), gameButton('Restart', reset, true));
    reset();
};

const buildOmamoriMatchGame = (ctx) => {
    const width = 7;
    const movesMax = 24;
    const pieces = ['Fuji', 'Rail', 'Tea', 'Soba', 'Onsen', 'Lamp'];
    let grid = [];
    let selected = null;
    let moves = movesMax;
    let score = 0;
    const board = document.createElement('div');
    board.className = 'game-board match-board';

    const randomPiece = () => gameSample(pieces);
    const indexOf = (x, y) => y * width + x;
    const adjacent = (a, b) => Math.abs((a % width) - (b % width)) + Math.abs(Math.floor(a / width) - Math.floor(b / width)) === 1;

    const findMatches = () => {
        const matches = new Set();

        for (let y = 0; y < width; y += 1) {
            for (let x = 0; x < width - 2; x += 1) {
                const value = grid[indexOf(x, y)];
                if (value && value === grid[indexOf(x + 1, y)] && value === grid[indexOf(x + 2, y)]) {
                    matches.add(indexOf(x, y));
                    matches.add(indexOf(x + 1, y));
                    matches.add(indexOf(x + 2, y));
                }
            }
        }

        for (let x = 0; x < width; x += 1) {
            for (let y = 0; y < width - 2; y += 1) {
                const value = grid[indexOf(x, y)];
                if (value && value === grid[indexOf(x, y + 1)] && value === grid[indexOf(x, y + 2)]) {
                    matches.add(indexOf(x, y));
                    matches.add(indexOf(x, y + 1));
                    matches.add(indexOf(x, y + 2));
                }
            }
        }

        return [...matches];
    };

    const collapse = () => {
        for (let x = 0; x < width; x += 1) {
            const column = [];
            for (let y = width - 1; y >= 0; y -= 1) {
                const value = grid[indexOf(x, y)];
                if (value) column.push(value);
            }
            while (column.length < width) column.push(randomPiece());
            for (let y = width - 1; y >= 0; y -= 1) {
                grid[indexOf(x, y)] = column[width - 1 - y];
            }
        }
    };

    const settle = (countScore = true) => {
        let matches = findMatches();
        while (matches.length > 0) {
            if (countScore) score += matches.length * 10;
            matches.forEach((index) => { grid[index] = null; });
            collapse();
            matches = findMatches();
        }
        ctx.setScore(score);
    };

    const render = () => {
        gameClear(board);
        grid.forEach((piece, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `game-tile${selected === index ? ' is-selected' : ''}`;
            button.textContent = piece;
            button.style.background = `color-mix(in srgb, var(--game-accent, #ca8a04) ${12 + (pieces.indexOf(piece) + 1) * 7}%, #fff)`;
            button.addEventListener('click', () => choose(index));
            board.appendChild(button);
        });
    };

    const choose = (index) => {
        if (moves <= 0) return;
        if (selected === null) {
            selected = index;
            render();
            return;
        }

        if (selected === index) {
            selected = null;
            render();
            return;
        }

        if (! adjacent(selected, index)) {
            selected = index;
            render();
            return;
        }

        [grid[selected], grid[index]] = [grid[index], grid[selected]];
        const matches = findMatches();

        if (matches.length === 0) {
            [grid[selected], grid[index]] = [grid[index], grid[selected]];
            selected = null;
            ctx.setStatus('No match');
            render();
            return;
        }

        moves -= 1;
        selected = null;
        settle(true);
        ctx.setStatus(`${moves} moves`);
        ctx.track('move', { game_score: score });

        if (moves <= 0) {
            ctx.setStatus('Done');
            ctx.track('complete', { game_result: 'done', game_score: score });
        }

        render();
    };

    const reset = () => {
        grid = Array.from({ length: width * width }, randomPiece);
        selected = null;
        moves = movesMax;
        score = 0;
        settle(false);
        ctx.setScore(0);
        ctx.setStatus(`${moves} moves`);
        render();
        ctx.track('start');
    };

    ctx.clear();
    ctx.stage.appendChild(board);
    ctx.controls.appendChild(gameButton('New charms', reset, true));
    reset();
};

const buildPrefectureTypingGame = (ctx) => {
    const terms = ['Hokkaido', 'Aomori', 'Miyagi', 'Tokyo', 'Kanagawa', 'Ishikawa', 'Nagano', 'Kyoto', 'Osaka', 'Hiroshima', 'Fukuoka', 'Okinawa', 'Shinkansen', 'Takkyubin', 'Onsen', 'Kaiseki'];
    let current = '';
    let score = 0;
    let remaining = 60;
    let timer = null;
    const panel = document.createElement('div');
    panel.className = 'typing-panel';
    const word = document.createElement('div');
    word.className = 'typing-word';
    const entry = document.createElement('form');
    entry.className = 'typing-entry';
    const input = document.createElement('input');
    input.placeholder = 'Type the word here';
    input.autocomplete = 'off';
    entry.appendChild(input);
    panel.append(word, entry);

    const nextTerm = () => {
        current = gameSample(terms);
        word.textContent = current;
        input.value = '';
    };

    const finish = () => {
        window.clearInterval(timer);
        timer = null;
        input.disabled = true;
        ctx.setStatus('Done');
        ctx.track('complete', { game_result: 'done', game_score: score });
    };

    const start = () => {
        if (timer) return;
        score = 0;
        remaining = 60;
        input.disabled = false;
        ctx.setScore(0);
        ctx.setStatus(`${remaining}s`);
        nextTerm();
        input.focus();
        ctx.track('start');
        timer = window.setInterval(() => {
            remaining -= 1;
            ctx.setStatus(`${remaining}s`);
            if (remaining <= 0) finish();
        }, 1000);
    };

    input.addEventListener('input', () => {
        if (! timer) return;
        if (input.value.trim().toLowerCase() !== current.toLowerCase()) return;
        score += 1;
        ctx.setScore(score);
        ctx.track('type', { game_score: score });
        nextTerm();
    });

    entry.addEventListener('submit', (event) => event.preventDefault());

    ctx.clear();
    ctx.stage.appendChild(panel);
    ctx.controls.append(gameButton('Start sprint', start), gameButton('Stop', finish, true));
    input.disabled = true;
    nextTerm();
};

const buildUkiyoSliderGame = (ctx) => {
    const size = 3;
    const imageUrl = new URL(`/${ctx.config.image}`, window.location.origin).toString();
    let tiles = [];
    let moves = 0;
    const board = document.createElement('div');
    board.className = 'game-board slider-board';

    const emptyIndex = () => tiles.indexOf(null);
    const adjacent = (a, b) => Math.abs((a % size) - (b % size)) + Math.abs(Math.floor(a / size) - Math.floor(b / size)) === 1;
    const solved = () => tiles.every((value, index) => (index === tiles.length - 1 ? value === null : value === index));

    const render = () => {
        gameClear(board);
        tiles.forEach((value, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `slider-tile${value === null ? ' is-empty' : ''}`;
            button.disabled = value === null;
            if (value !== null) {
                const x = value % size;
                const y = Math.floor(value / size);
                button.style.backgroundImage = `url("${imageUrl}")`;
                button.style.backgroundSize = `${size * 100}% ${size * 100}%`;
                button.style.backgroundPosition = `${x * 50}% ${y * 50}%`;
            }
            button.addEventListener('click', () => move(index));
            board.appendChild(button);
        });
    };

    const move = (index) => {
        const empty = emptyIndex();
        if (! adjacent(index, empty)) return;
        [tiles[index], tiles[empty]] = [tiles[empty], tiles[index]];
        moves += 1;
        const score = ctx.setScore(Math.max(0, 1000 - moves * 12));
        ctx.setStatus(`${moves} moves`);

        if (solved()) {
            ctx.setStatus('Won');
            ctx.track('complete', { game_result: 'win', game_score: score });
        }

        render();
    };

    const shuffleSolvable = () => {
        tiles = [0, 1, 2, 3, 4, 5, 6, 7, null];
        for (let step = 0; step < 90; step += 1) {
            const empty = emptyIndex();
            const options = tiles
                .map((_, index) => index)
                .filter((index) => adjacent(index, empty));
            const pick = gameSample(options);
            [tiles[pick], tiles[empty]] = [tiles[empty], tiles[pick]];
        }
    };

    const reset = () => {
        shuffleSolvable();
        moves = 0;
        ctx.setScore(0);
        ctx.setStatus('Ready');
        render();
        ctx.track('start');
    };

    ctx.clear();
    ctx.stage.appendChild(board);
    ctx.controls.appendChild(gameButton('Shuffle', reset, true));
    reset();
};

const buildRamenRushGame = (ctx) => {
    const ingredients = ['Miso', 'Shoyu', 'Noodles', 'Egg', 'Corn', 'Chashu', 'Negi', 'Nori'];
    let order = [];
    let selected = [];
    let score = 0;
    let streak = 0;
    let remaining = 75;
    let timer = null;
    const panel = document.createElement('div');
    panel.className = 'ramen-panel';
    const orderNode = document.createElement('div');
    orderNode.className = 'ramen-order';
    const selectedNode = document.createElement('div');
    selectedNode.className = 'ramen-selected';
    const ingredientGrid = document.createElement('div');
    ingredientGrid.className = 'ramen-ingredient-grid';
    panel.append(orderNode, selectedNode, ingredientGrid);

    const nextOrder = () => {
        const length = score >= 80 ? 4 : 3;
        order = gameShuffle(ingredients).slice(0, length);
        selected = [];
        render();
    };

    const renderChips = (node, items, activeItems = []) => {
        gameClear(node);
        items.forEach((item) => {
            const chip = document.createElement('span');
            chip.className = `ramen-chip${activeItems.includes(item) ? ' is-selected' : ''}`;
            chip.textContent = item;
            node.appendChild(chip);
        });
    };

    const render = () => {
        orderNode.textContent = order.join(' + ');
        renderChips(selectedNode, selected, selected);
        gameClear(ingredientGrid);
        ingredients.forEach((ingredient) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `ramen-chip${selected.includes(ingredient) ? ' is-selected' : ''}`;
            button.textContent = ingredient;
            button.addEventListener('click', () => {
                if (! timer) return;
                selected = selected.includes(ingredient)
                    ? selected.filter((item) => item !== ingredient)
                    : [...selected, ingredient];
                render();
            });
            ingredientGrid.appendChild(button);
        });
    };

    const sameOrder = () => order.length === selected.length && order.every((item) => selected.includes(item));

    const serve = () => {
        if (! timer) return;
        if (sameOrder()) {
            streak += 1;
            score += 10 + streak * 2;
            ctx.setScore(score);
            ctx.setStatus(`${remaining}s`);
            ctx.track('serve', { game_score: score });
            nextOrder();
            return;
        }

        streak = 0;
        score = Math.max(0, score - 5);
        ctx.setScore(score);
        ctx.setStatus('Retry');
    };

    const finish = () => {
        window.clearInterval(timer);
        timer = null;
        ctx.setStatus('Done');
        ctx.track('complete', { game_result: 'done', game_score: score });
    };

    const start = () => {
        if (timer) return;
        score = 0;
        streak = 0;
        remaining = 75;
        ctx.setScore(0);
        ctx.setStatus(`${remaining}s`);
        nextOrder();
        ctx.track('start');
        timer = window.setInterval(() => {
            remaining -= 1;
            ctx.setStatus(`${remaining}s`);
            if (remaining <= 0) finish();
        }, 1000);
    };

    ctx.clear();
    ctx.stage.appendChild(panel);
    ctx.controls.append(
        gameButton('Start shift', start),
        gameButton('Serve', serve, true),
        gameButton('Clear bowl', () => { selected = []; render(); }, true),
        gameButton('End', finish, true),
    );
    nextOrder();
};

const gameBuilders = {
    'daily-japan-word': buildDailyWordGame,
    'fuji-merge-2048': buildFujiMergeGame,
    'sushi-snake': buildSushiSnakeGame,
    'torii-memory-match': buildToriiMemoryGame,
    'sakura-minesweeper': buildSakuraMinesweeperGame,
    'tokyo-metro-dash': buildTokyoMetroDashGame,
    'omamori-match-three': buildOmamoriMatchGame,
    'prefecture-typing': buildPrefectureTypingGame,
    'ukiyo-e-slider': buildUkiyoSliderGame,
    'ramen-order-rush': buildRamenRushGame,
};

const initJapanGames = () => {
    document.querySelectorAll('[data-japan-game]').forEach((root) => {
        const ctx = buildGameContext(root);
        const builder = gameBuilders[ctx.slug];

        if (typeof builder !== 'function') {
            ctx.setStatus('Unavailable');
            return;
        }

        builder(ctx);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJapanGames, { once: true });
} else {
    initJapanGames();
}

<?php

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Sorry! This script can only be run from the command line. Have fun playing the game!"]);
    exit;
}

/**
 * This is a board game called Tic-Tac-Toe. The game is played on a 3x3 grid.
 * ENTITY: Board (Bàn cờ)
 */
class Board {
    public array $grid;

    public function __construct() {
        $this->resetBoard();
    }

    /**
     * Reset the board to its initial state.
     */
    public function resetBoard(): void {
        $this->grid = array_fill(0, 3, array_fill(0, 3, null));
    }

    /**
     * Display the board.
     */
    public function display(): void {
        echo "\n";
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $cell = $this->grid[$i][$j] ?? " ";
                echo " $cell ";
                if ($j < 2) echo "|";
            }
            echo "\n";
            if ($i < 2) echo "---+---+---\n";
        }
        echo "\n";
    }

    /**
     * Set a move on the board.
     * @param int $row
     * @param int $col
     * @param string $symbol
     * @return bool
     */
    public function setMove(int $row, int $col, string $symbol): bool {
        if ($this->grid[$row][$col] === null) {
            $this->grid[$row][$col] = $symbol;
            return true;
        }
        return false;
    }

    /**
     * Check if the board is full.
     * @return bool
     */
    public function isFull(): bool {
        foreach ($this->grid as $row) {
            if (in_array(null, $row, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if there is a winner.
     * @return string|null
     */
    public function checkWinner(): ?string {
        for ($i = 0; $i < 3; $i++) {
            if ($this->grid[$i][0] !== null && 
                $this->grid[$i][0] === $this->grid[$i][1] && 
                $this->grid[$i][1] === $this->grid[$i][2]) {
                return $this->grid[$i][0]; 
            }
        }

        for ($j = 0; $j < 3; $j++) {
            if ($this->grid[0][$j] !== null && 
                $this->grid[0][$j] === $this->grid[1][$j] && 
                $this->grid[1][$j] === $this->grid[2][$j]) {
                return $this->grid[0][$j];
            }
        }

        if ($this->grid[0][0] !== null && 
            $this->grid[0][0] === $this->grid[1][1] && 
            $this->grid[1][1] === $this->grid[2][2]) {
            return $this->grid[0][0];
        }

        if ($this->grid[0][2] !== null && 
            $this->grid[0][2] === $this->grid[1][1] && 
            $this->grid[1][1] === $this->grid[2][0]) {
            return $this->grid[0][2]; 
        }

        return null; 
    }
}
/**
 * Player represents a player in the game.
 * ENTITY: Player (Người chơi)
 */
class Player {
    private string $name;
    private string $symbol;

    public function __construct(string $name, string $symbol) {
        $this->name = $name;
        $this->symbol = $symbol;
    }

    /**
     * Get the player's symbol.
     * @return string
     */
    public function getSymbol(): string {
        return $this->symbol;
    }

    /**
     * Get the player's name.
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
}

/**
 * AIPlayer represents an AI player in the game.
 * ENTITY: AIPlayer (Người chơi AI)
 */
class AIPlayer {
    private string $symbol;
    private string $opponentSymbol;
    private int $difficulty;

    public function __construct(string $symbol, int $difficulty = 3) {
        $this->symbol = $symbol;
        $this->opponentSymbol = ($symbol === "X") ? "O" : "X";
        $this->difficulty = $difficulty;
    }

    /**
     * Get the AI player's symbol.
     * @return string
     */
    public function getSymbol(): string {
        return $this->symbol;
    }

    /**
     * Get the AI player's move.
     * @param Board $board
     * @return array
     */
    public function getAIMove(Board $board): array {
        $chance = rand(1, 100);
        if ($this->difficulty == 1 && $chance <= 90) {
            return $this->randomMove($board);
        } elseif ($this->difficulty == 2 && $chance <= 50) {
            return $this->smartMove($board);
        } else {
            return $this->minimaxMove($board);
        }
    }

    /**
     * Get a random move.
     * @param Board $board
     * @return array
     */
    private function randomMove(Board $board): array {
        $emptyCells = [];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($board->grid[$i][$j] === null) {
                    $emptyCells[] = [$i, $j];
                }
            }
        }
        return $emptyCells[array_rand($emptyCells)];
    }

    /**
     * Get a smart move.
     * @param Board $board
     * @return array
     */
    private function smartMove(Board $board): array {
        return $this->randomMove($board);
    }

    /**
     * Get a move using the minimax algorithm.
     * @param Board $board
     * @return array
     */
    private function minimaxMove(Board $board): array {
        return $this->randomMove($board);
    }
}

/**
 * Game Engine is the core of the game. It initializes the game, runs the game loop, and handles the game state.
 * CORE: Game Engine (Lõi của game)
 */
class GameEngine {
    private Board $board;
    private array $players;
    private int $currentTurn;
    private GameState $state;
    private InputHandler $inputHandler;
    private ?AIPlayer $aiPlayer = null;

    public function __construct() {
        $this->inputHandler = new InputHandler();
        $this->initializeGame();
    }

    /**
     * Initialize the game.
     */
    private function initializeGame(): void {
        $this->board = new Board();
        echo "Chọn chế độ chơi (1) Người vs Người | (2) Người vs AI: ";
        $mode = trim(fgets(STDIN));

        if ($mode == "2") {
            echo "Chọn độ khó AI (1=Dễ, 2=Trung bình, 3=Khó): ";
            $difficulty = (int)trim(fgets(STDIN));
            $this->players = [new Player("Người chơi", "X"), null];
            $this->aiPlayer = new AIPlayer("O", $difficulty);
        } else {
            $this->players = [new Player("Người chơi 1", "X"), new Player("Người chơi 2", "O")];
        }
        $this->currentTurn = 0;
        $this->state = new GameState();
    }

    /**
     * Run the game loop.
     */
    public function run(): void {
        do {
            $this->playGame();
        } while ($this->askForRestart());
    }

    /**
     * Play the game.
     */
    private function playGame(): void {
        while (!$this->state->isGameOver()) {
            Renderer::clearScreen();
            $this->board->display();
    
            if ($this->aiPlayer && $this->currentTurn == 1) {
                [$row, $col] = $this->aiPlayer->getAIMove($this->board);
                if ($row == -1 && $col == -1) {
                    echo "AI can't move, board full.\n";
                    $this->state->setGameOver();
                    return;
                }
                echo "AI's turn ({$this->aiPlayer->getSymbol()}):\n";
                if ($this->board->setMove($row, $col, $this->aiPlayer->getSymbol())) {
                    $winner = $this->board->checkWinner();
                    if ($winner) {
                        Renderer::clearScreen();
                        $this->board->display();
                        echo "🎉 AI wins!\n";
                        $this->state->setGameOver();
                        return;
                    }
    
                    if ($this->board->isFull()) {
                        Renderer::clearScreen();
                        $this->board->display();
                        echo "🤝 It's a draw!\n";
                        $this->state->setGameOver();
                        return;
                    }
    
                    $this->currentTurn = 1 - $this->currentTurn;
                } else {
                    echo "❌ Invalid move. Try again.\n";
                }
            } else {
                echo "{$this->players[$this->currentTurn]->getName()}'s turn ({$this->players[$this->currentTurn]->getSymbol()}):\n";
                [$row, $col] = $this->inputHandler->getPlayerMove();
    
                if ($this->board->setMove($row, $col, $this->players[$this->currentTurn]->getSymbol())) {
                    $winner = $this->board->checkWinner();
                    if ($winner) {
                        Renderer::clearScreen();
                        $this->board->display();
                        echo "🎉 {$this->players[$this->currentTurn]->getName()} wins!\n";
                        $this->state->setGameOver();
                        return;
                    }
    
                    if ($this->board->isFull()) {
                        Renderer::clearScreen();
                        $this->board->display();
                        echo "🤝 It's a draw!\n";
                        $this->state->setGameOver();
                        return;
                    }
    
                    $this->currentTurn = 1 - $this->currentTurn;
                } else {
                    echo "❌ Invalid move. Try again.\n";
                }
            }
        }
    }
    
    /**
     * Ask the player if they want to play again.
     * @return bool
     */
    private function askForRestart(): bool {
        echo "\n🔄 Do you want to play again? (y/n): ";
        $input = trim(fgets(STDIN));

        if (strtolower($input) === "y") {
            $this->initializeGame();
            return true;
        }

        echo "👋 Thanks for playing! Goodbye.\n";
        return false;
    }
}

/**
 * InputHandler handles the user input for the game.
 * CORE: Input Handler (Xử lý đầu vào)
 */
class InputHandler {
    /**
     * Get the player's move from the terminal.
     * @return array
     */
    public function getPlayerMove(): array {
        while (true) {
            echo "Enter your move (row(0-2) and column(0-2), e.g., 1 2): ";
            $input = trim(fgets(STDIN));
            $parts = explode(" ", $input);

            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $row = (int)$parts[0];
                $col = (int)$parts[1];

                if ($row >= 0 && $row < 3 && $col >= 0 && $col < 3) {
                    return [$row, $col];
                }
            }
            echo "Invalid input. Please enter two numbers between 0 and 2.\n";
        }
    }
}

/**
 * GameState represents the state of the game.
 * CORE: Game State (Trạng thái của game)
 */
class GameState {
    private bool $gameOver = false;

    public function isGameOver(): bool {
        return $this->gameOver;
    }

    public function setGameOver(): void {
        $this->gameOver = true;
    }
}

/**
 * Renderer provides rendering functions for the game.
 * CORE: Renderer (Bộ vẽ)
 */
class Renderer {
    /**
     * Clear the terminal screen.
     * @return void
     */
    public static function clearScreen(): void {
        echo "\033[2J\033[H";
    }
}

// 🚀 Start game. Have fun !
$game = new GameEngine();
$game->run();
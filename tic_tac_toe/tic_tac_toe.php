<?php
class Board {
    private array $grid;

    public function __construct() {
        $this->resetBoard();
    }

    public function resetBoard(): void {
        $this->grid = array_fill(0, 3, array_fill(0, 3, null));
    }

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

    public function setMove(int $row, int $col, string $symbol): bool {
        if ($this->grid[$row][$col] === null) {
            $this->grid[$row][$col] = $symbol;
            return true;
        }
        return false;
    }

    public function isFull(): bool {
        foreach ($this->grid as $row) {
            if (in_array(null, $row, true)) {
                return false;
            }
        }
        return true;
    }

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

class Player {
    private string $name;
    private string $symbol;

    public function __construct(string $name, string $symbol) {
        $this->name = $name;
        $this->symbol = $symbol;
    }

    public function getSymbol(): string {
        return $this->symbol;
    }

    public function getName(): string {
        return $this->name;
    }
}

class GameEngine {
    private Board $board;
    private array $players;
    private int $currentTurn;
    private GameState $state;
    private InputHandler $inputHandler;

    public function __construct() {
        $this->inputHandler = new InputHandler();
        $this->initializeGame();
    }

    private function initializeGame(): void {
        $this->board = new Board();
        $this->players = [
            new Player("Player 1", "X"),
            new Player("Player 2", "O")
        ];
        $this->currentTurn = 0;
        $this->state = new GameState();
    }

    public function run(): void {
        do {
            $this->playGame();
        } while ($this->askForRestart());
    }

    private function playGame(): void {
        while (!$this->state->isGameOver()) {
            Renderer::clearScreen();
            $this->board->display();
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

class InputHandler {
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

class GameState {
    private bool $gameOver = false;

    public function isGameOver(): bool {
        return $this->gameOver;
    }

    public function setGameOver(): void {
        $this->gameOver = true;
    }
}

class Renderer {
    public static function clearScreen(): void {
        echo "\033[2J\033[H";
    }
}

$game = new GameEngine();
$game->run();

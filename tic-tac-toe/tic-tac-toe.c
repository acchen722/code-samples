#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <stdbool.h>

#include <time.h>
#include <stdlib.h>

#define USAGE_ERROR -22
#define MEMORY_ERROR -12

// Shortcut for smaller/smallest data type, without implication of ASCII character
typedef unsigned char byte;
typedef enum {
  PLAYER_HUMAN,
  PLAYER_COMPUTER
} PlayerType;
typedef enum {
  CELL_EMPTY, // 0x00/00/0
  CELL_X,     // 0x01/01/1
  CELL_O      // 0x02/10/2
} CellType;

typedef struct GameOptions GameOptions;

struct GameOptions {
  // Allows INT_MAX rows, columns, but will not optimize for large sizes
  // Allows non-square boards, where stretching across the board is a win
  unsigned int n_rows;
  unsigned int n_columns;
  
  PlayerType type_player1;
  PlayerType type_player2;
};

typedef struct Board Board;

struct Board {
  // Copy of GameOptions, but allows for passing single variable with all data
  unsigned int n_rows;
  unsigned int n_columns;
  
  byte** data;
};

// reused error functions with standard linux error codes
void usageError(char* program_name, char* error_message);
void memoryError();
// malloc and calloc wrappers to check for (un)successful requests
void* mallocOrExit(unsigned long long size);
void* callocOrExit(unsigned long long blocks, unsigned long long size);
// processes and validates command line parameters, throws one of above errors
GameOptions* validateInput(int argc, char** argv);
// essentially board constructor, initializes empty board of given size
Board* initializeBoard(unsigned int n_rows, unsigned int n_columns);
// board cell functions
// setCell sets unconditionally, checks are made in game-logic
void setCell(Board* board, unsigned int row, unsigned int col, CellType value);
CellType getCell(Board* board, unsigned int row, unsigned int col);
// prints the board with appropriate spacing and separators
void printBoard(Board* board);
// checks if a (newly-placed) cell results in a win
// optimal AI may be implemented using this function, using a graph of distance-to-win
bool boardWinningMove(Board* board, unsigned int row, unsigned int col);
// essentially board destructor
void cleanBoard(Board* board);
// skeleton of AI move, currently picks random or first unfilled space
// also used as draw check
bool computerMove(Board* board, unsigned int* outRow, unsigned int* outCol);

// debug and basic test functions, enabled by -DDEBUG flag (clang tested)
#ifdef DEBUG
void printBoardRaw(Board* board);
void testWinCalc(GameOptions* options);
#endif

int main(int argc, char** argv){
  GameOptions* options = validateInput(argc, argv);
    
  // compiled with -DDEBUG
  #ifdef DEBUG
  printf("%u %u %s %s\n", options->n_rows, options->n_columns,
    (options->type_player1 == PLAYER_HUMAN) ? "HUMAN" : "COMPUTER",
    (options->type_player2 == PLAYER_HUMAN) ? "HUMAN" : "COMPUTER");
  #endif
  
  Board* board = initializeBoard(options->n_rows, options->n_columns);
  
  #ifdef DEBUG
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      setCell(board, row, col, (row % 4) > (col % 4) ? (row % 4) : (col % 4));
    }
  }
  printBoardRaw(board);
  printBoard(board);
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col <= board->n_columns >> 2; col += 1){
      printf("%u ", board->data[row][col]);
    }
    printf("\n");
  }
  free(board);
  board = initializeBoard(options->n_rows, options->n_columns);
  
  testWinCalc(options);
  #endif
    
  printBoard(board);
  bool player_1_turn = true;
  unsigned long long possible_moves = board->n_rows * board->n_columns;
  unsigned long long input;
  unsigned int r, c;
  srand(time(NULL));
  int winner = 0;
  do {
    if ((player_1_turn && options->type_player1 == PLAYER_HUMAN)
      || (!player_1_turn && options->type_player2 == PLAYER_HUMAN)){
      printf("move (1-%lld): ", possible_moves);
      char input_str[21];
      fgets(input_str, 20, stdin);
      
      if (sscanf(input_str, "%lld", &input) != 1 || input <= 0 || input > possible_moves){
        printf("Please select a valid space (1-%lld):\n", possible_moves);
        continue;
      }
      
      input -= 1;
      r = input / board->n_columns;
      c = input % board->n_columns;
      if (getCell(board, r, c) != CELL_EMPTY){
        printf("Please select an empty space:\n");
        continue;
      }
    } else {
      computerMove(board, &r, &c);
      printf("Computer plays: %lld\n",
        (unsigned long long)(r*board->n_columns + c));
    }
    
    setCell(board, r, c, (player_1_turn)? CELL_X : CELL_O);
    printBoard(board);
    winner = (player_1_turn)?1:2;
    player_1_turn = !player_1_turn;
    unsigned int throwaway;
    if (!computerMove(board, &throwaway, &throwaway)){
      winner = -1;
      break;
    }
  } while (!boardWinningMove(board, r, c));
  
  if (winner == -1){
    printf("No moves left remaining, draw!\n");
  } else {
    printf("Player %d wins!\n", winner);
  }
  
  cleanBoard(board);
  free(options);
  return 0;
}

void usageError(char* program_name, char* error_message){
  fprintf(stderr, "error: %s\n", error_message);
  fprintf(stderr, "usage: %s <N>x<M> <human|computer> <human|computer>\n", program_name);
  exit(USAGE_ERROR);
}

void memoryError(){
  fprintf(stderr, "Insufficient memory\n");
  exit(MEMORY_ERROR);
}

void* mallocOrExit(unsigned long long size){
  void* new_memory = malloc(size);
  if (new_memory == NULL){
    memoryError();
  }
  return new_memory;
}

void* callocOrExit(unsigned long long blocks, unsigned long long size){
  void* new_memory = calloc(blocks, size);
  if (new_memory == NULL){
    memoryError();
  }
  return new_memory;
}

GameOptions* validateInput(int argc, char** argv){
  // Sets global program name for aesthetic reasons and DRY
  // If none given (edge case or manipulation), default value set in usageError()
  char* program_name = (argc > 0) ? argv[0] : "<program name>";
  
  // Local variables for at-end malloc due to possible exit mid-processing
  long n_rows, n_columns;
  PlayerType p1, p2;
  
  // Always constant number of arguments given
  if (argc != 4){
    usageError(program_name, "Invalid number of arguments");
  }
  
  // No negative or zero allowed, but arbitrary large sizes may be entered
  if (sscanf(argv[1], "%ldx%ld", &n_rows, &n_columns) != 2
    || n_rows <= 0 || n_columns <= 0){
    usageError(program_name, "Invalid board size");
  }
  
  bool p1_is_human = (strcmp(argv[2], "human") == 0);
  bool p1_is_computer = (strcmp(argv[2], "computer") == 0);
  bool p2_is_human = (strcmp(argv[3], "human") == 0);
  bool p2_is_computer = (strcmp(argv[3], "computer") == 0);
  if ((!p1_is_human && !p1_is_computer) || (!p2_is_human && !p2_is_computer)){
    usageError(program_name, "Players must be 'human' or 'computer'");
  } else {
    p1 = (p1_is_human) ? (PLAYER_HUMAN) : (PLAYER_COMPUTER);
    p2 = (p2_is_human) ? (PLAYER_HUMAN) : (PLAYER_COMPUTER);
  }
  
  // if this runs out of memory, something has gone very wrong
  GameOptions* options = mallocOrExit(1 * sizeof(GameOptions));
  options->n_rows = n_rows;
  options->n_columns = n_columns;
  options->type_player1 = p1;
  options->type_player2 = p2;
  
  return options;
}

Board* initializeBoard(unsigned int n_rows, unsigned int n_columns){
  Board* board = mallocOrExit(1 * sizeof(Board));
  board->n_rows = n_rows;
  board->n_columns = n_columns;
  
  // number of rows unchanged
  // sizeof byte/char always 1, but explicit call for clarity
  board->data = mallocOrExit(n_rows * sizeof(byte*));
  
  // stores each cell in a 2-bit section of a byte
  // 00/0: empty,   01/1: x,    10/2: o,  11/3: unused
  // 2 rshift == int division by 4, adds 1 regardless for remainder
  unsigned int column_bytes = (n_columns >> 2) + 1;
  for (int row = 0; row < n_rows; row += 1){
    board->data[row] = callocOrExit(column_bytes, sizeof(byte));
  }
  return board;
}

void setCell(Board* board, unsigned int row, unsigned int col, CellType value){
  // integer division/round-down of column by 4
  unsigned int col_byte = col >> 2;
  // gets last three bytes of col, gives bit offset of 2-wide position
  byte offset = (2*(col & 0x03));
  // bitmask retains all but the position to be overwritten
  // checking assumed to have been done by getCell separately in gamelogic
  board->data[row][col_byte] &= ~(0xc0 >> offset);
  // shifts value into position and ORs it into place
  board->data[row][col_byte] |= value << (6-offset);
}

CellType getCell(Board* board, unsigned int row, unsigned int col){
  // integer division/round-down of column by 4
  unsigned int col_byte = col >> 2;
  // gets last three bytes of col, gives bit offset of 2-wide position
  byte offset = (2*(col & 0x03));
  // bitmask retains specified position
  byte value = board->data[row][col_byte] & (0xc0 >> offset);
  return (CellType)(value >> (6-offset));
}

#ifdef DEBUG
void printBoardRaw(Board* board){
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      printf("%u ", getCell(board, row, col));
    }
    printf("\n");
  }
}
#endif

void printBoard(Board* board){
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      char cell;
      switch (getCell(board, row, col)){
        case CELL_EMPTY: cell = ' '; break;
        case CELL_X: cell = 'X'; break;
        case CELL_O: cell = 'O'; break;
        default:
          cell = '?';
      }
      printf("%c", cell);
      if (col != board->n_columns - 1){
        printf("|");
      }
    }
    printf("\n");
    if (row != board->n_rows - 1){
      for (unsigned int col = 0; col < board->n_columns*2 - 1; col += 1){
        printf("-");
      }
      printf("\n");
    }
  }
}

bool boardWinningMove(Board* board, unsigned int row, unsigned int col){
  CellType cell_type = getCell(board, row, col);
  bool won_game = true;
  // check row
  for (int r = 0; r < board->n_rows; r += 1){
    if (getCell(board, r, col) != cell_type){
      won_game = false;
      break;
    }
  }
  
  // check column
  if (!won_game){
    won_game = true;
    for (int c = 0; c < board->n_columns; c += 1){
      if (getCell(board, row, c) != cell_type){
        won_game = false;
        break;
      }
    }
  }
  
  signed long shortest_side = (board->n_rows < board->n_columns)
    ? board->n_rows : board->n_columns;
  unsigned int diag_matches = 0;
  
  // check down-right diagonal
  if (!won_game){
    won_game = true;
    for (int off = -shortest_side; off <= shortest_side; off += 1){
      signed long r = row + off;
      signed long c = col + off;
      if (r < 0 || r >= board->n_rows || c < 0 || c >= board->n_columns){
        continue;
      }
      if (getCell(board, r, c) != cell_type){
        won_game = false;
        break;
      }
      diag_matches += 1;
      won_game = (diag_matches == shortest_side);
    }
  }
  
  // check up-right diagonal
  if (!won_game){
    won_game = true;
    diag_matches = 0;
    for (int off = -shortest_side; off <= shortest_side; off += 1){
      signed long r = row - off;
      signed long c = col + off;
      if (r < 0 || r >= board->n_rows || c < 0 || c >= board->n_columns){
        continue;
      }
      if (getCell(board, r, c) != cell_type){
        won_game = false;
        break;
      }
      diag_matches += 1;
      won_game = (diag_matches == shortest_side);
    }
  }
  
  return won_game;
}

void cleanBoard(Board* board){
  for (int r = 0; r < board->n_rows; r += 1){
    free(board->data[r]);
  }
  free(board->data);
  free(board);
}

// function also used to check if a draw
bool computerMove(Board* board, unsigned int* outRow, unsigned int* outCol){
  unsigned int r, c;
  r = rand() % board->n_rows;
  c = rand() % board->n_columns;
  bool empty_space_found = true;
  if (getCell(board, r, c) != CELL_EMPTY){
    empty_space_found = false;
    for (unsigned int row = 0; row < board->n_rows; row += 1){
      for (unsigned int col = 0; col < board->n_columns; col += 1){
        if (getCell(board, row, col) == CELL_EMPTY){
          r = row;
          c = col;
          empty_space_found = true;
        }
      }
      if (empty_space_found){
        break;
      }
    }
  }
  *outRow = r;
  *outCol = c;
  return empty_space_found;
}

#ifdef DEBUG
void testWinCalc(GameOptions* options){
  Board* board = initializeBoard(options->n_rows, options->n_columns);
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      setCell(board, row, col, (row % 4) > (col % 4) ? (row % 4) : (col % 4));
    }
  }
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      printf("%c ", boardWinningMove(board, row, col) ? '+' : '-') ;
    }
    printf("\n");
  }
  free(board);
  board = initializeBoard(options->n_rows, options->n_columns);
  printf("\n");
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      setCell(board, row, col, (row == col || row == board->n_columns-(col+1)) ? (1) : (0));
    }
  }
  for (unsigned int row = 0; row < board->n_rows; row += 1){
    for (unsigned int col = 0; col < board->n_columns; col += 1){
      printf("%c ", boardWinningMove(board, row, col) ? '+' : '-') ;
    }
    printf("\n");
  }
  free(board);
  board = initializeBoard(options->n_rows, options->n_columns);
}
#endif
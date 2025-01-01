export function victory_status(tiles, victoryShape, playerId) {
    const possibleVictorySets = getPossibleVictorySets(victoryShape);
    
    const board = tiles.reduce((acc, tile) => {
        acc[tile.space] = tile.playerId;
        return acc;
    }, {});

    const winning_set = possibleVictorySets.find(set => {
        const winning_spaces_occupied = set.reduce((count, space) => {
            return count + (board[space] === playerId ? 1 : 0);
        }, 0);
        
        return winning_spaces_occupied === 4;
    });

    if (winning_set) {
        console.log('winning_set', winning_set, 'playerId', playerId, 'board', board, 'tiles', tiles);
    }

    return {
        has_won: !!winning_set,
        winning_spaces: winning_set ?? []
    };
}

function getPossibleVictorySets(victory_shape) {
    const possible_victories = {
        'square': SQUARE_VICTORIES,
        'line': LINE_VICTORIES,
        'zig': ZIG_VICTORIES,
        'el': EL_VICTORIES,
        'pyramid': PYRAMID_VICTORIES
    };

    return possible_victories[victory_shape] || [];
}

const SQUARE_VICTORIES = [
    [1, 2, 5, 6],
    [2, 3, 6, 7],
    [3, 4, 7, 8],
    [5, 6, 9, 10],
    [6, 7, 10, 11],
    [7, 8, 11, 12],
    [9, 10, 13, 14],
    [10, 11, 14, 15],
    [11, 12, 15, 16],
];

const LINE_VICTORIES = [
    [1, 5, 9, 13],
    [2, 6, 10, 14],
    [3, 7, 11, 15],
    [4, 8, 12, 16],
    [1, 2, 3, 4],
    [5, 6, 7, 8],
    [9, 10, 11, 12],
    [13, 14, 15, 16],
];

const EL_VICTORIES = [
    // X X X
    //     X
    [1, 2, 3, 7],
    [2, 3, 4, 8],
    [5, 6, 7, 11],
    [6, 7, 8, 12],
    [9, 10, 11, 15],
    [10, 11, 12, 16],

    // X
    // X X X
    [1, 5, 6, 7],
    [2, 6, 7, 8],
    [5, 9, 10, 11],
    [6, 10, 11, 12],
    [9, 13, 14, 15],
    [10, 14, 15, 16],

    //     X
    // X X X
    [3, 5, 6, 7],
    [4, 6, 7, 8],
    [7, 9, 10, 11],
    [8, 10, 11, 12],
    [11, 13, 14, 15],
    [12, 14, 15, 16],

    // X X X
    // X
    [1, 2, 3, 5],
    [2, 3, 4, 6],
    [5, 6, 7, 9],
    [6, 7, 8, 10],
    [9, 10, 11, 13],
    [10, 11, 12, 14],

    // X X
    // X
    // X
    [1, 2, 5, 9],
    [2, 3, 6, 10],
    [3, 4, 7, 11],
    [5, 6, 9, 13],
    [6, 7, 10, 14],
    [7, 8, 11, 15],

    // X X
    //   X
    //   X
    [1, 2, 6, 10],
    [2, 3, 7, 11],
    [3, 4, 8, 12],
    [5, 6, 10, 14],
    [6, 7, 11, 15],
    [7, 8, 12, 16],

    // X
    // X
    // X X
    [1, 5, 9, 10],
    [2, 6, 10, 11],
    [3, 7, 11, 12],
    [5, 9, 13, 14],
    [6, 10, 14, 15],
    [7, 11, 15, 16],

    //   X
    //   X
    // X X
    [2, 6, 9, 10],
    [3, 7, 10, 11],
    [4, 8, 11, 12],
    [6, 10, 13, 14],
    [7, 11, 14, 15],
    [8, 12, 15, 16],
];

const PYRAMID_VICTORIES = [
    // X X X
    //   X
    [1, 2, 3, 6],
    [2, 3, 4, 7],
    [5, 6, 7, 10],
    [6, 7, 8, 11],
    [9, 10, 11, 14],
    [10, 11, 12, 15],

    //   X
    // X X X
    [2, 5, 6, 7],
    [3, 6, 7, 8],
    [6, 9, 10, 11],
    [7, 10, 11, 12],
    [10, 13, 14, 15],
    [11, 14, 15, 16],
    
    // X
    // X X
    // X
    [1, 5, 6, 9],
    [2, 6, 7, 10],
    [3, 7, 8, 11],
    [5, 9, 10, 13],
    [6, 10, 11, 14],
    [7, 11, 12, 15],

    //   X
    // X X
    //   X
    [2, 5, 6, 10],
    [3, 6, 7, 11],
    [4, 7, 8, 12],
    [6, 9, 10, 14],
    [7, 10, 11, 15],
    [8, 11, 12, 16],
];

const ZIG_VICTORIES = [
    // X X
    //   X X
    [1, 2, 6, 7],
    [2, 3, 7, 8],
    [5, 6, 10, 11],
    [6, 7, 11, 12],
    [9, 10, 14, 15],
    [10, 11, 15, 16],

    //   X X
    // X X
    [2, 3, 5, 6],
    [3, 4, 6, 7],
    [6, 7, 9, 10],
    [7, 8, 10, 11],
    [10, 11, 13, 14],
    [11, 12, 14, 15],

    // X
    // X X
    //   X
    [1, 5, 6, 10],
    [2, 6, 7, 11],
    [3, 7, 8, 12],
    [5, 9, 10, 14],
    [6, 10, 11, 15],
    [7, 11, 12, 16],

    //   X
    // X X
    // X
    [2, 5, 6, 9],
    [3, 6, 7, 10],
    [4, 7, 8, 11],
    [6, 9, 10, 13],
    [7, 10, 11, 14],
    [8, 11, 12, 15],
];
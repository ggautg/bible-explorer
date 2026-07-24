import GraphController from './GraphController'
import ReaderController from './ReaderController'
import SearchController from './SearchController'
import FavoriteController from './FavoriteController'
import NoteController from './NoteController'
import TextSearchController from './TextSearchController'
const Bible = {
    GraphController: Object.assign(GraphController, GraphController),
ReaderController: Object.assign(ReaderController, ReaderController),
SearchController: Object.assign(SearchController, SearchController),
FavoriteController: Object.assign(FavoriteController, FavoriteController),
NoteController: Object.assign(NoteController, NoteController),
TextSearchController: Object.assign(TextSearchController, TextSearchController),
}

export default Bible
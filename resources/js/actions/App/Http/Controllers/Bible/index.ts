import GraphController from './GraphController'
import ReaderController from './ReaderController'
import SearchController from './SearchController'
import FavoriteController from './FavoriteController'
const Bible = {
    GraphController: Object.assign(GraphController, GraphController),
ReaderController: Object.assign(ReaderController, ReaderController),
SearchController: Object.assign(SearchController, SearchController),
FavoriteController: Object.assign(FavoriteController, FavoriteController),
}

export default Bible
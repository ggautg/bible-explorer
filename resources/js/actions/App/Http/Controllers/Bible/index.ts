import GraphController from './GraphController'
import SearchController from './SearchController'
import ReaderController from './ReaderController'
const Bible = {
    GraphController: Object.assign(GraphController, GraphController),
SearchController: Object.assign(SearchController, SearchController),
ReaderController: Object.assign(ReaderController, ReaderController),
}

export default Bible
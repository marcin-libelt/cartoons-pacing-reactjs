import React from 'react'
import { HTML5Backend } from 'react-dnd-html5-backend'
import { DndProvider } from 'react-dnd'
import Container from './container'

const App = function({ parameter }) {
    return (
        <React.StrictMode>
            <DndProvider backend={HTML5Backend}>
                <Container data={parameter}/>
            </DndProvider>
        </React.StrictMode>
    )
}

export default App;


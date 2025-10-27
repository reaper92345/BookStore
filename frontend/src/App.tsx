import React from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import Home from './pages/Home';
import BookList from './components/BookList';

const App: React.FC = () => {
  return (
    <Router>
      <Switch>
        <Route path="/" exact component={Home} />
        <Route path="/books" component={BookList} />
      </Switch>
    </Router>
  );
};

export default App;
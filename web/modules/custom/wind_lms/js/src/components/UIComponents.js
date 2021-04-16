
'use strict';

import React from "react";
import _ from 'lodash';

export function UserTeamBadge(props){
  // Iterate thru props.currentUserTeams array to check if any object has a property of tid that is the same the prop.tid
  // Highlight your team.
  let highlightedTextClass = _.some(props.currentUserTeams, ['tid', props.tid]) ? 'text-info' : '';

  return (
    <span className="badge badge-pill badge-outline badge-secondary mr-1 mb-1" >
      {props.hasOwnProperty('ancestors') &&
        props.ancestors.map((term, index) => {

          // Iterate thru props.currentUserTeams array to check if any object has a property of tid that is the same the term.tid
          let txtClass = _.some(props.currentUserTeams, ['tid', term.tid]) ? 'text-info' : 'text-secondary';

          return (
            <span className={`font-weight-lighter mr-1 ${txtClass}`} key={index} data-tid={term.tid}>
              {term.label} /
            </span>
          );
        })
      }

      <span className={highlightedTextClass} data-tid={props.tid}>{props.label}</span>
    </span>
  );
}

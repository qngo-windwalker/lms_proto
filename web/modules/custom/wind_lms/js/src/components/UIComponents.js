
'use strict';

import React from "react";
import _ from 'lodash';

export function UserTeamBadge(props){
  // Highlight your team
  let highlightedTextClass = props.tid == props.currentUserTeamTid ? 'text-info' : '';

  return (
    <span className="badge badge-pill badge-outline badge-secondary mr-1 mb-1" >
      {props.hasOwnProperty('ancestors') &&
        props.ancestors.map((term, index) => {

          let txtClass = 'text-secondary';
          if (term.tid == props.currentUserTeamTid) {
            // Highlight your team
            txtClass = 'text-info';
          }

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

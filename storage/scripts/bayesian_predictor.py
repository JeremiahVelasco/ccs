#!/usr/bin/env python3
"""
Bayesian Project Completion Predictor

This script implements a Bayesian network for predicting project completion probability
based on various project features like task progress, team collaboration, faculty approval,
and timeline adherence.
"""

import sys
import json
import logging
import traceback
from typing import Dict, Any, Optional, List
from pathlib import Path

try:
    from pgmpy.models import BayesianNetwork
    from pgmpy.factors.discrete import TabularCPD
    from pgmpy.inference import VariableElimination
    import numpy as np
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing required dependencies: {str(e)}. Please install pgmpy and numpy."
    }))
    sys.exit(1)

# Configure logging - suppress stdout logging when called from Laravel
logging.basicConfig(
    level=logging.ERROR,  # Only show errors to avoid mixing with JSON output
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

class ProjectCompletionPredictor:
    """
    Bayesian Network for predicting project completion probability
    """
    
    def __init__(self, config_path: Optional[str] = None):
        """
        Initialize the Bayesian network predictor
        
        Args:
            config_path: Optional path to configuration file
        """
        self.config = self._load_config(config_path)
        self.model = None
        self.inference = None
        self._build_network()
    
    def _load_config(self, config_path: Optional[str]) -> Dict[str, Any]:
        """Load configuration from file or use defaults"""
        default_config = {
            "network_structure": [
                ('task_progress', 'project_completion'),
                ('team_collaboration', 'project_completion'),
                ('timeline_adherence', 'project_completion')
            ],
            "node_cardinalities": {
                'task_progress': 3,
                'team_collaboration': 3,
                'timeline_adherence': 3,
                'project_completion': 2
            },
            "prior_probabilities": {
                'task_progress': [0.3, 0.5, 0.2],  # Low, Medium, High
                'team_collaboration': [0.2, 0.6, 0.2],  # Poor, Good, Excellent
                'timeline_adherence': [0.35, 0.5, 0.15]  # Behind, On Track, Ahead
            },
            "use_learning": False,
            "validation_enabled": True
        }
        
        if config_path and Path(config_path).exists():
            try:
                with open(config_path, 'r') as f:
                    user_config = json.load(f)
                    default_config.update(user_config)
                    logger.info(f"Loaded configuration from {config_path}")
            except Exception as e:
                logger.warning(f"Failed to load config from {config_path}: {e}")
        
        return default_config
    
    def _build_network(self) -> None:
        """Build the Bayesian network structure"""
        try:
            # Create network structure
            self.model = BayesianNetwork(self.config['network_structure'])
            
            # Define and add CPDs
            cpds = self._create_cpds()
            self.model.add_cpds(*cpds)
            
            # Validate network
            if not self.model.check_model():
                raise ValueError("Bayesian network model validation failed")
            
            # Create inference engine
            self.inference = VariableElimination(self.model)
            
            # logger.info("Bayesian network built successfully")  # Commented out for clean JSON output
            
        except Exception as e:
            logger.error(f"Failed to build Bayesian network: {e}")
            raise
    
    def _create_cpds(self) -> List[TabularCPD]:
        """Create Conditional Probability Distributions"""
        cpds = []
        
        # Task Progress CPD
        task_progress_cpd = TabularCPD(
            variable='task_progress',
            variable_card=3,
            values=np.array(self.config['prior_probabilities']['task_progress']).reshape(3, 1)
        )
        cpds.append(task_progress_cpd)
        
        # Team Collaboration CPD
        team_collab_cpd = TabularCPD(
            variable='team_collaboration',
            variable_card=3,
            values=np.array(self.config['prior_probabilities']['team_collaboration']).reshape(3, 1)
        )
        cpds.append(team_collab_cpd)
        
        # Timeline Adherence CPD
        timeline_cpd = TabularCPD(
            variable='timeline_adherence',
            variable_card=3,
            values=np.array(self.config['prior_probabilities']['timeline_adherence']).reshape(3, 1)
        )
        cpds.append(timeline_cpd)
        
        # Project Completion CPD (depends on all parent variables)
        project_completion_cpd = self._create_project_completion_cpd()
        cpds.append(project_completion_cpd)
        
        return cpds
    
    def _create_project_completion_cpd(self) -> TabularCPD:
        """Create the main project completion CPD with improved probabilities"""
        
        # More realistic probability distributions based on project management research
        # Format: task_progress (0,1,2) x team_collab (0,1,2) x timeline (0,1,2)
        # 3 x 3 x 3 = 27 combinations
        
        # Failure probabilities (more nuanced based on combinations)
        failure_probs = [
            # task=0 (Low), team=0 (Poor), timeline=0,1,2 (Behind, On Track, Ahead)
            [0.95, 0.92, 0.88],
            # task=0 (Low), team=1 (Good), timeline=0,1,2
            [0.90, 0.85, 0.80],
            # task=0 (Low), team=2 (Excellent), timeline=0,1,2
            [0.85, 0.78, 0.70],
            
            # task=1 (Medium), team=0 (Poor), timeline=0,1,2
            [0.80, 0.75, 0.68],
            # task=1 (Medium), team=1 (Good), timeline=0,1,2
            [0.70, 0.62, 0.55],
            # task=1 (Medium), team=2 (Excellent), timeline=0,1,2
            [0.65, 0.55, 0.45],
            
            # task=2 (High), team=0 (Poor), timeline=0,1,2
            [0.65, 0.55, 0.45],
            # task=2 (High), team=1 (Good), timeline=0,1,2
            [0.50, 0.38, 0.28],
            # task=2 (High), team=2 (Excellent), timeline=0,1,2
            [0.40, 0.28, 0.18],
        ]
        
        # Flatten the failure probabilities
        flat_failure_probs = []
        for prob_list in failure_probs:
            flat_failure_probs.extend(prob_list)
        
        # Success probabilities are complement of failure probabilities
        success_probs = [1 - p for p in flat_failure_probs]
        
        # Combine into the CPD format [failure_row, success_row]
        cpd_values = [flat_failure_probs, success_probs]
        
        # Convert to numpy array and ensure proper shape
        cpd_values = np.array(cpd_values)
        
        return TabularCPD(
            variable='project_completion',
            variable_card=2,
            values=cpd_values,
            evidence=['task_progress', 'team_collaboration', 'timeline_adherence'],
            evidence_card=[3, 3, 3]
        )
    
    def validate_input(self, evidence: Dict[str, Any]) -> Dict[str, Any]:
        """
        Validate and clean input evidence
        
        Args:
            evidence: Dictionary of evidence values
            
        Returns:
            Validated evidence dictionary
            
        Raises:
            ValueError: If validation fails
        """
        if not self.config.get('validation_enabled', True):
            return evidence
        
        required_variables = ['task_progress', 'team_collaboration', 'timeline_adherence']
        
        # Check for missing variables
        missing_vars = [var for var in required_variables if var not in evidence]
        if missing_vars:
            raise ValueError(f"Missing required variables: {missing_vars}")
        
        # Validate ranges
        validation_rules = {
            'task_progress': [0, 1, 2],
            'team_collaboration': [0, 1, 2],
            'timeline_adherence': [0, 1, 2]
        }
        
        validated_evidence = {}
        for var, value in evidence.items():
            if var in validation_rules:
                if value not in validation_rules[var]:
                    raise ValueError(f"Invalid value for {var}: {value}. Must be one of {validation_rules[var]}")
                validated_evidence[var] = value
        
        return validated_evidence
    
    def predict_completion_probability(self, evidence: Dict[str, Any]) -> float:
        """
        Predict project completion probability given evidence
        
        Args:
            evidence: Dictionary with keys: task_progress, team_collaboration, timeline_adherence
        
        Returns:
            Completion probability as float
        """
        try:
            # Validate input
            validated_evidence = self.validate_input(evidence)
            
            # Perform inference
            result = self.inference.query(
                variables=['project_completion'],
                evidence=validated_evidence
            )
            
            # Get completion probability (state=1)
            completion_prob = result.values[1]
            
            # Ensure probability is within valid range
            completion_prob = max(0.0, min(1.0, float(completion_prob)))
            
            # logger.info(f"Prediction successful: {completion_prob:.4f}")  # Commented out for clean JSON output
            return completion_prob
            
        except Exception as e:
            logger.error(f"Prediction failed: {e}")
            # Return a reasonable default based on input if possible
            return self._get_fallback_probability(evidence)
    
    def _get_fallback_probability(self, evidence: Dict[str, Any]) -> float:
        """
        Calculate fallback probability when inference fails
        
        Args:
            evidence: Original evidence dictionary
            
        Returns:
            Fallback probability
        """
        try:
            # Simple heuristic based on input values
            scores = []
            
            # Task progress contribution
            task_score = evidence.get('task_progress', 0) / 2.0
            scores.append(task_score)
            
            # Team collaboration contribution
            team_score = evidence.get('team_collaboration', 0) / 2.0
            scores.append(team_score)
            
            # Timeline adherence contribution
            timeline_score = evidence.get('timeline_adherence', 0) / 2.0
            scores.append(timeline_score)
            
            # Weighted average (can be improved with domain knowledge)
            weights = [0.4, 0.3, 0.3]  # task_progress has higher weight
            fallback_prob = sum(w * s for w, s in zip(weights, scores))
            
            return max(0.1, min(0.9, fallback_prob))  # Constrain to reasonable range
            
        except Exception:
            return 0.5  # Ultimate fallback
    
    def get_model_info(self) -> Dict[str, Any]:
        """
        Get information about the model structure
        
        Returns:
            Model information dictionary
        """
        return {
            'nodes': list(self.model.nodes()),
            'edges': list(self.model.edges()),
            'cpds': [cpd.variable for cpd in self.model.get_cpds()],
            'config': self.config
        }

def main():
    """Main function to handle command line interface"""
    # Set up argument validation
    if len(sys.argv) != 2:
        print(json.dumps({
            "success": False,
            "error": "Invalid arguments. Usage: python bayesian_predictor.py '<json_evidence>'"
        }))
        return
    
    try:
        # Parse input from Laravel
        input_data = json.loads(sys.argv[1])
        
        # Create predictor
        predictor = ProjectCompletionPredictor()
        
        # Make prediction
        probability = predictor.predict_completion_probability(input_data)
        
        # Prepare response
        response = {
            "success": True,
            "completion_probability": probability,
            "completion_percentage": round(probability * 100, 2),
            "model_info": {
                "nodes": len(predictor.model.nodes()),
                "edges": len(predictor.model.edges()),
                "inference_engine": "VariableElimination"
            }
        }
        
        print(json.dumps(response))
        
    except json.JSONDecodeError as e:
        print(json.dumps({
            "success": False,
            "error": f"Invalid JSON input: {str(e)}"
        }))
    except ValueError as e:
        print(json.dumps({
            "success": False,
            "error": f"Input validation failed: {str(e)}"
        }))
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        logger.error(traceback.format_exc())
        print(json.dumps({
            "success": False,
            "error": f"Prediction failed: {str(e)}"
        }))

if __name__ == "__main__":
    main()
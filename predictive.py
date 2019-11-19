# Importing modules
import pandas as pd
import numpy as np
import sys

from matplotlib import pyplot as plt
from pysurvival.datasets import Dataset
from sklearn.model_selection import train_test_split
from pysurvival.models.simulations import SimulationModel
from pysurvival.models.multi_task import LinearMultiTaskModel
from pysurvival.utils.metrics import concordance_index
from pysurvival.utils.display import correlation_matrix
from pysurvival.utils.display import display_loss_values
from pysurvival.utils.display import integrated_brier_score
from pysurvival.utils.display import compare_to_actual
from pysurvival.utils.display import display_baseline_simulations
from pysurvival.utils.display import create_risk_groups



# Reading the dataset
# raw_dataset = Dataset('maintenance').load()
raw_dataset = pd.read_csv ('sample.csv', sep=",")
print("The raw_dataset has the following shape: {}.".format(raw_dataset.shape))
print(raw_dataset.head(3))
print(raw_dataset.info())


# Defining the time and event column
time_column = 'minutes_working_since_last_maintenance'
event_column = 'maintenance'

# Encoding the categorical variables as one-hot vectors
categories = ['team', 'provider']
dataset = pd.get_dummies(raw_dataset, columns = categories, drop_first=True)


# Defining the modeling features
features = np.setdiff1d(dataset.columns, [time_column, event_column]).tolist()


# correlation_matrix(dataset[features], figure_size=(15, 5))


# Checking for null values
N_null = sum(dataset[features].isnull().sum())
print("The dataset contains {} null values".format(N_null)) #0 null values

# Removing duplicates if there exist
N_dupli = sum(dataset.duplicated(keep='first'))
dataset = dataset.drop_duplicates(keep='first').reset_index(drop=True)
print("The dataset contains {} duplicates".format(N_dupli))


# Number of samples in the dataset
N = dataset.shape[0]


# Building training and testing sets
index_train, index_test = train_test_split( range(N), test_size = 0.4)
data_train = dataset.loc[index_train].reset_index( drop = True )
data_test  = dataset.loc[index_test].reset_index( drop = True )


# Creating the X, T and E inputs
X_train, X_test = data_train[features], data_test[features]
T_train, T_test = data_train[time_column], data_test[time_column]
E_train, E_test = data_train[event_column], data_test[event_column]


# Initializing the MTLR with a time axis split into 300 intervals
linear_mtlr = LinearMultiTaskModel(bins=300)


# Fitting the model
linear_mtlr.fit(
    X_train, 
    T_train, 
    E_train, 
    num_epochs = 1000,
    init_method = 'orthogonal', 
    optimizer ='rmsprop', 
    lr = 1e-3, 
    l2_reg = 3, 
    l2_smooth = 3,
)


# display_loss_values(linear_mtlr, figure_size=(7, 4))
# results = compare_to_actual(linear_mtlr, X_test, T_test, E_test, is_at_risk = False,  figure_size=(16, 6), metrics = ['rmse', 'mean', 'median'])

                
c_index = concordance_index(linear_mtlr, X_test, T_test, E_test)
print('C-index: {:.2f}'.format(c_index)) #0.92


# Initializing the figure
fig, ax = plt.subplots(figsize=(8, 4))


# Saving the time of event
choices = np.argwhere((E_test==1.)&(T_test>=1)).flatten()
k = np.random.choice( choices, 1)[0]
t = T_test[k]


# Calculate survival
survival = linear_mtlr.predict_survival(X_test.values[k, :]).flatten()

print (survival)
system.end ()

# Displaying the functions
plt.plot(linear_mtlr.times, survival, color = "blue", label="test", lw=2)


# Actual time
plt.axvline(x=t, color="black", ls ='--')
ax.annotate('T={:.1f}'.format(t), xy=(t, 0.5*(1.+0.2)), xytext=(t, 0.5*(1.+0.2)), fontsize=12)    



# Show everything
plt.legend(fontsize=12)
plt.title("test", fontsize=15)
plt.ylim(0, 1.05)
plt.show()


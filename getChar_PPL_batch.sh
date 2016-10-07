T1="test";
T2=".txt";
TEMPFILE=$T1$RANDOM$T2;
MODELFILE=$1
DATA="$2";

echo $DATA > /mnt/matiss/torch/char-rnn/test/$TEMPFILE;

/home/ubuntu/torch/install/bin/th /mnt/matiss/torch/char-rnn/measure_perplexity.lua $MODELFILE \
-data_path /mnt/matiss/torch/char-rnn/test/$TEMPFILE \
-verbose 0 | grep "Perplexity per word:"
	
rm /mnt/matiss/torch/char-rnn/test/$TEMPFILE;